#!/usr/bin/env python3
"""카페24 www 최상위 목록을 읽기 전용으로 확인한다."""

from __future__ import annotations

import stat
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
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
        for entry in sorted(sftp.listdir_attr("www"), key=lambda item: item.filename.casefold()):
            kind = "폴더" if stat.S_ISDIR(entry.st_mode) else "파일"
            print(f"{kind}\t{entry.st_size}\t{entry.filename}")
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
