#!/usr/bin/env python3
"""카페24 SFTP에서 파일 구조만 읽어 로컬 비공개 트리로 저장한다."""

from __future__ import annotations

import os
import stat
import sys
from pathlib import Path, PurePosixPath

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import (  # noqa: E402
    DEFAULT_CONFIG,
    DEFAULT_OUTPUT,
    DEFAULT_SECRETS,
    Entry,
    ensure_private_file,
    read_env,
    render_tree,
)


def main() -> int:
    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    password = secrets.get("FTP_PASSWORD", "")
    if not password:
        raise RuntimeError("로컬 비밀 설정에 FTP 비밀번호가 없습니다.")

    known_hosts = PROJECT_ROOT / ".private" / "known_hosts"
    if not known_hosts.exists():
        raise RuntimeError("서버 호스트 키가 확인되지 않았습니다.")

    client = paramiko.SSHClient()
    client.load_host_keys(str(known_hosts))
    client.set_missing_host_key_policy(paramiko.RejectPolicy())
    client.connect(
        hostname=config["FTP_HOST"],
        port=int(config.get("SFTP_PORT", "22")),
        username=config["FTP_USER"],
        password=password,
        look_for_keys=False,
        allow_agent=False,
        timeout=20,
        auth_timeout=20,
    )

    root = PurePosixPath(".")
    entries: list[Entry] = [Entry(root, True)]
    sftp = client.open_sftp()

    def visit(directory: PurePosixPath, depth: int) -> None:
        if depth >= 20:
            return
        children = sorted(
            sftp.listdir_attr(str(directory)),
            key=lambda item: (not stat.S_ISDIR(item.st_mode), item.filename.casefold()),
        )
        for child in children:
            path = directory / child.filename
            is_dir = stat.S_ISDIR(child.st_mode)
            entries.append(Entry(path, is_dir, None if is_dir else child.st_size))
            if is_dir:
                visit(path, depth + 1)

    try:
        visit(root, 0)
    finally:
        sftp.close()
        client.close()

    ensure_private_file(DEFAULT_OUTPUT)
    DEFAULT_OUTPUT.write_text(render_tree(entries, root), encoding="utf-8")
    os.chmod(DEFAULT_OUTPUT, stat.S_IRUSR | stat.S_IWUSR)
    print(f"파일 트리를 저장했습니다: {DEFAULT_OUTPUT}")
    print(f"항목 수: {len(entries) - 1}")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except (OSError, RuntimeError, paramiko.SSHException) as exc:
        print(f"오류: {type(exc).__name__}", file=sys.stderr)
        raise SystemExit(1)
