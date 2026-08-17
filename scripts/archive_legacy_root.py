#!/usr/bin/env python3
"""옛 첫 화면 파일을 www/aaa로 옮기고 새 index.html만 기본 진입점으로 남긴다."""

from __future__ import annotations

import hashlib
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

ARCHIVE_DIR = "www/aaa"
MOVES = (
    ("www/appleicon.png", f"{ARCHIVE_DIR}/appleicon.png"),
    ("www/favicon.ico", f"{ARCHIVE_DIR}/favicon.ico"),
    ("www/index.css", f"{ARCHIVE_DIR}/index.css"),
    ("www/index.png", f"{ARCHIVE_DIR}/index.png"),
    ("www/index1.js", f"{ARCHIVE_DIR}/index1.js"),
    ("www/index1.html", f"{ARCHIVE_DIR}/index1-original.html"),
    ("www/index1.php", f"{ARCHIVE_DIR}/index1.php"),
)


def exists(sftp: paramiko.SFTPClient, path: str) -> bool:
    try:
        sftp.stat(path)
        return True
    except FileNotFoundError:
        return False


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    local_main = (PROJECT_ROOT / "index.php").read_bytes()
    local_old = PROJECT_ROOT / ".private" / "remote-analysis" / "www" / "index.html"

    client = paramiko.SSHClient()
    client.load_host_keys(str(PROJECT_ROOT / ".private" / "known_hosts"))
    client.set_missing_host_key_policy(paramiko.RejectPolicy())
    client.connect(
        config["FTP_HOST"],
        port=int(config.get("SFTP_PORT", "22")),
        username=config["FTP_USER"],
        password=secrets["FTP_PASSWORD"],
        look_for_keys=False,
        allow_agent=False,
        timeout=20,
    )

    sftp = client.open_sftp()
    try:
        with sftp.open("www/index.html", "rb") as remote_file:
            remote_main = remote_file.read()
        if hashlib.sha256(remote_main).digest() != hashlib.sha256(local_main).digest():
            raise RuntimeError("현재 index.html이 검증한 새 화면과 다릅니다.")
        if exists(sftp, ARCHIVE_DIR):
            raise RuntimeError("www/aaa가 이미 있어 자동 이동을 중단했습니다.")

        for source, _ in MOVES:
            if not exists(sftp, source):
                raise RuntimeError(f"옮길 파일이 없습니다: {source}")

        sftp.mkdir(ARCHIVE_DIR)
        for source, target in MOVES:
            if exists(sftp, target):
                raise RuntimeError(f"보존 대상이 이미 있습니다: {target}")
            sftp.rename(source, target)
            print(f"이동: {source} -> {target}")

        archive_main = f"{ARCHIVE_DIR}/index.html"
        temporary = f"{archive_main}.uploading"
        sftp.put(str(local_old), temporary)
        sftp.rename(temporary, archive_main)
        if sftp.stat(archive_main).st_size != local_old.stat().st_size:
            raise RuntimeError("aaa/index.html 업로드 크기가 일치하지 않습니다.")
        print(f"옛 화면 진입점: {archive_main}")
    finally:
        sftp.close()
        client.close()
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (OSError, RuntimeError, paramiko.SSHException) as exc:
        print(f"오류: {type(exc).__name__}: {exc}", file=sys.stderr)
        raise SystemExit(1)
