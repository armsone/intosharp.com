#!/usr/bin/env python3
"""검증을 마친 새 index.php를 기본 첫 화면으로 전환한다."""

from __future__ import annotations

import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

REQUIRED = ("www/index.php", "www/index1.html", "www/legacy-catalog.js")
OLD_ENTRY_FILES = ("www/index.html", "www/index1.php")


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
        for remote in REQUIRED:
            if sftp.stat(remote).st_size <= 0:
                raise RuntimeError(f"필수 파일이 비어 있습니다: {remote}")

        for remote in OLD_ENTRY_FILES:
            try:
                sftp.remove(remote)
                print(f"기존 진입 파일 정리: {remote}")
            except FileNotFoundError:
                print(f"이미 정리됨: {remote}")
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
