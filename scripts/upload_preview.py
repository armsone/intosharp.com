#!/usr/bin/env python3
"""새 첫 화면과 보존한 옛 첫 화면을 카페24에 안전하게 올린다."""

from __future__ import annotations

import hashlib
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

UPLOADS = (
    (PROJECT_ROOT / "index.php", "www/index.html"),
    (PROJECT_ROOT / "legacy-catalog.js", "www/legacy-catalog.js"),
    (PROJECT_ROOT / "admin.js", "www/admin.js"),
    (PROJECT_ROOT / "admin-api.php", "www/admin-api.php"),
    (PROJECT_ROOT / "favicon.svg", "www/favicon.svg"),
    (PROJECT_ROOT / "apple-touch-icon.png", "www/apple-touch-icon.png"),
    (PROJECT_ROOT / "icon-192.png", "www/icon-192.png"),
    (PROJECT_ROOT / "icon-512.png", "www/icon-512.png"),
    (PROJECT_ROOT / "modern.js", "www/modern.js"),
    (PROJECT_ROOT / "sw.js", "www/sw.js"),
    (PROJECT_ROOT / "manifest.json", "www/manifest.json"),
)


def digest(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)

    for local, _ in UPLOADS:
        if not local.is_file():
            raise RuntimeError(f"업로드할 파일이 없습니다: {local.name}")

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
        for local, remote in UPLOADS:
            temporary = f"{remote}.uploading"
            sftp.put(str(local), temporary)
            try:
                sftp.posix_rename(temporary, remote)
            except OSError:
                # 일부 카페24 SFTP 서버는 원자적 덮어쓰기 확장을 지원하지 않는다.
                # 대상 파일에 직접 전송하고 남은 임시 파일만 정리한다.
                sftp.put(str(local), remote)
                try:
                    sftp.remove(temporary)
                except FileNotFoundError:
                    pass

            local_size = local.stat().st_size
            remote_size = sftp.stat(remote).st_size
            if local_size != remote_size:
                raise RuntimeError(f"업로드 크기 불일치: {remote}")
            print(f"업로드 확인: {remote} ({remote_size} bytes, sha256 {digest(local)[:12]})")
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
