#!/usr/bin/env python3
"""SSH 터널로 MySQL을 읽어 로컬 SQL 백업과 초기화 계획을 만든다."""

from __future__ import annotations

import os
import select
import socketserver
import stat
import sys
import threading
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402
import pymysql  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

BACKUP = PROJECT_ROOT / ".private" / "backups" / "intosharp-db-2026-08-15.sql"
PLAN = PROJECT_ROOT / ".private" / "db-initialization-plan.txt"


class ForwardHandler(socketserver.BaseRequestHandler):
    def handle(self) -> None:
        transport = self.server.ssh_transport  # type: ignore[attr-defined]
        channel = transport.open_channel(
            "direct-tcpip", ("127.0.0.1", 3306), self.request.getpeername()
        )
        if channel is None:
            return
        try:
            while True:
                readable, _, _ = select.select([self.request, channel], [], [], 10)
                if self.request in readable:
                    data = self.request.recv(65536)
                    if not data:
                        break
                    channel.sendall(data)
                if channel in readable:
                    data = channel.recv(65536)
                    if not data:
                        break
                    self.request.sendall(data)
        finally:
            channel.close()


class ForwardServer(socketserver.ThreadingTCPServer):
    allow_reuse_address = True
    daemon_threads = True


def quote_identifier(value: str) -> str:
    return "`" + value.replace("`", "``") + "`"


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    BACKUP.parent.mkdir(parents=True, exist_ok=True)
    os.chmod(BACKUP.parent, stat.S_IRWXU)

    ssh = paramiko.SSHClient()
    ssh.load_host_keys(str(PROJECT_ROOT / ".private" / "known_hosts"))
    ssh.set_missing_host_key_policy(paramiko.RejectPolicy())
    ssh.connect(
        config["FTP_HOST"],
        port=int(config.get("SFTP_PORT", "22")),
        username=config["FTP_USER"],
        password=secrets["FTP_PASSWORD"],
        look_for_keys=False,
        allow_agent=False,
        timeout=20,
    )
    transport = ssh.get_transport()
    if transport is None:
        raise RuntimeError("SSH 전송 연결을 만들 수 없습니다.")

    server = ForwardServer(("127.0.0.1", 0), ForwardHandler)
    server.ssh_transport = transport  # type: ignore[attr-defined]
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()

    connection = None
    try:
        connection = pymysql.connect(
            host="127.0.0.1",
            port=server.server_address[1],
            user=config["DB_USER"],
            password=secrets["DB_PASSWORD"],
            database=config.get("DB_NAME", config["DB_USER"]),
            charset="utf8mb4",
            autocommit=True,
            connect_timeout=20,
        )
        cursor = connection.cursor()
        cursor.execute("SHOW FULL TABLES")
        objects = [(str(row[0]), str(row[1])) for row in cursor.fetchall()]

        plan_lines = [f"DB: {config.get('DB_NAME', config['DB_USER'])}"]
        with BACKUP.open("w", encoding="utf-8", newline="\n") as output:
            output.write("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n")
            for name, kind in objects:
                identifier = quote_identifier(name)
                if kind.upper() == "VIEW":
                    cursor.execute(f"SHOW CREATE VIEW {identifier}")
                    create_sql = str(cursor.fetchone()[1])
                    output.write(f"DROP VIEW IF EXISTS {identifier};\n{create_sql};\n\n")
                    plan_lines.append(f"VIEW\t{name}")
                    continue

                cursor.execute(f"SELECT COUNT(*) FROM {identifier}")
                row_count = int(cursor.fetchone()[0])
                cursor.execute(f"SHOW CREATE TABLE {identifier}")
                create_sql = str(cursor.fetchone()[1])
                output.write(f"DROP TABLE IF EXISTS {identifier};\n{create_sql};\n")

                cursor.execute(f"SELECT * FROM {identifier}")
                while True:
                    rows = cursor.fetchmany(250)
                    if not rows:
                        break
                    for row in rows:
                        values = ",".join(connection.escape(value) for value in row)
                        output.write(f"INSERT INTO {identifier} VALUES ({values});\n")
                output.write("\n")
                plan_lines.append(f"TABLE\t{name}\t{row_count}")
            output.write("SET FOREIGN_KEY_CHECKS=1;\n")

        PLAN.write_text("\n".join(plan_lines) + "\n", encoding="utf-8")
        os.chmod(BACKUP, stat.S_IRUSR | stat.S_IWUSR)
        os.chmod(PLAN, stat.S_IRUSR | stat.S_IWUSR)
        print(f"DB 객체 수: {len(objects)}")
        print(f"SQL 백업 크기: {BACKUP.stat().st_size} bytes")
    finally:
        if connection is not None:
            connection.close()
        server.shutdown()
        server.server_close()
        ssh.close()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
