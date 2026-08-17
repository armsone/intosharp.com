#!/usr/bin/env python3
"""카페24 DATA 백업을 로컬 비공개 폴더에 내려받아 크기를 검증한다."""

from __future__ import annotations

import hashlib
import os
import stat
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

REMOTE = "DataBackup/intosharp-2026-08-15.tar.gz"
LOCAL = PROJECT_ROOT / ".private" / "backups" / "intosharp-2026-08-15.tar.gz"


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    LOCAL.parent.mkdir(parents=True, exist_ok=True)
    os.chmod(LOCAL.parent, stat.S_IRWXU)

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
        remote_size = sftp.stat(REMOTE).st_size
        sftp.get(REMOTE, str(LOCAL))
    finally:
        sftp.close()
        client.close()

    os.chmod(LOCAL, stat.S_IRUSR | stat.S_IWUSR)
    local_size = LOCAL.stat().st_size
    if local_size != remote_size:
        raise RuntimeError("DATA 백업 크기 검증에 실패했습니다.")

    digest = hashlib.sha256(LOCAL.read_bytes()).hexdigest()
    print(f"DATA 백업 완료: {local_size} bytes")
    print(f"SHA-256: {digest}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
