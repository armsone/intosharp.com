#!/usr/bin/env python3
"""확정된 보존 목록을 제외한 카페24 DATA를 계획하거나 삭제한다."""

from __future__ import annotations

import argparse
import os
import stat
import sys
from pathlib import Path, PurePosixPath

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT / ".private" / "python-packages"))

import paramiko  # noqa: E402

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, read_env  # noqa: E402

KEEP_TREES = (
    PurePosixPath(".well-known"),
    PurePosixPath("www/.well-known"),
    PurePosixPath("www/zangzip"),
    PurePosixPath("DataBackup"),
)
KEEP_FILES = (
    PurePosixPath("www/.htaccess"),
    PurePosixPath("www/index.html"),
    PurePosixPath("www/index.css"),
    PurePosixPath("www/index1.js"),
    PurePosixPath("www/index.png"),
    PurePosixPath("www/appleicon.png"),
    PurePosixPath("www/favicon.ico"),
)
PLAN = PROJECT_ROOT / ".private" / "remote-cleanup-plan.txt"
LOCAL_DATA_BACKUP = (
    PROJECT_ROOT / ".private" / "backups" / "intosharp-2026-08-15.tar.gz"
)


def is_within(path: PurePosixPath, tree: PurePosixPath) -> bool:
    return path == tree or tree in path.parents


def is_kept(path: PurePosixPath) -> bool:
    root_system_file = len(path.parts) == 1 and path.name.startswith(".")
    return (
        root_system_file
        or path in KEEP_FILES
        or any(is_within(path, tree) for tree in KEEP_TREES)
    )


def is_keep_ancestor(path: PurePosixPath) -> bool:
    targets = (*KEEP_TREES, *KEEP_FILES)
    return any(path in target.parents for target in targets)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--execute", action="store_true")
    args = parser.parse_args()

    if args.execute and LOCAL_DATA_BACKUP.stat().st_size != 48_649_832:
        raise RuntimeError("검증된 로컬 DATA 백업이 없어 삭제를 중단했습니다.")

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
    files: list[PurePosixPath] = []
    directories: list[PurePosixPath] = []

    def scan(directory: PurePosixPath) -> None:
        for entry in sftp.listdir_attr(str(directory)):
            path = directory / entry.filename if str(directory) != "." else PurePosixPath(entry.filename)
            if is_kept(path):
                continue
            if stat.S_ISDIR(entry.st_mode):
                if is_keep_ancestor(path):
                    scan(path)
                else:
                    collect_subtree(path)
            else:
                files.append(path)

    def collect_subtree(directory: PurePosixPath) -> None:
        for entry in sftp.listdir_attr(str(directory)):
            path = directory / entry.filename
            if stat.S_ISDIR(entry.st_mode):
                collect_subtree(path)
            else:
                files.append(path)
        directories.append(directory)

    try:
        for required in (*KEEP_TREES, *KEEP_FILES):
            sftp.stat(str(required))
        scan(PurePosixPath("."))

        lines = [
            "서버 계정 최상위 숨김 설정 파일: 보존",
            "확정 보존 트리:",
            *(f"KEEP_TREE\t{path}" for path in KEEP_TREES),
            "확정 보존 파일:",
            *(f"KEEP_FILE\t{path}" for path in KEEP_FILES),
            f"삭제 파일 수: {len(files)}",
            f"삭제 디렉터리 수: {len(directories)}",
            "삭제 대상:",
            *(f"DELETE_FILE\t{path}" for path in sorted(files, key=str)),
            *(f"DELETE_DIR\t{path}" for path in sorted(directories, key=str)),
        ]
        PLAN.write_text("\n".join(lines) + "\n", encoding="utf-8")
        os.chmod(PLAN, stat.S_IRUSR | stat.S_IWUSR)

        if args.execute:
            for path in files:
                sftp.remove(str(path))
            for path in sorted(directories, key=lambda item: len(item.parts), reverse=True):
                sftp.rmdir(str(path))
    finally:
        sftp.close()
        client.close()

    print(f"삭제 파일 수: {len(files)}")
    print(f"삭제 디렉터리 수: {len(directories)}")
    print("실행 완료" if args.execute else "계획만 생성")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
