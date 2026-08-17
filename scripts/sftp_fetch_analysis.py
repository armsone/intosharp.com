#!/usr/bin/env python3
"""첫 화면 의존성 분석에 필요한 작은 텍스트 파일만 SFTP로 내려받는다."""

from __future__ import annotations

import os
import stat
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

REMOTE_FILES = (
    "www/.htaccess",
    "www/index.html",
    "www/index.php",
    "www/index.css",
    "www/index.js",
    "www/index1.js",
    "www/_common.php",
    "www/_head.php",
    "www/_tail.php",
    "www/head.php",
    "www/tail.php",
)


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    destination = PROJECT_ROOT / ".private" / "remote-analysis"
    destination.mkdir(parents=True, exist_ok=True)
    os.chmod(destination, stat.S_IRWXU)

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
        for remote in REMOTE_FILES:
            local = destination / remote
            local.parent.mkdir(parents=True, exist_ok=True)
            try:
                sftp.get(remote, str(local))
            except FileNotFoundError:
                continue
            os.chmod(local, stat.S_IRUSR | stat.S_IWUSR)
    finally:
        sftp.close()
        client.close()

    print("첫 화면 분석 파일을 비공개 경로에 저장했습니다.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
