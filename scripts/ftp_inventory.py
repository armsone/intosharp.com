#!/usr/bin/env python3
"""인투샵 호스팅의 파일 구조를 읽기 전용으로 조사한다."""

from __future__ import annotations

import argparse
import ftplib
import os
import stat
import sys
from dataclasses import dataclass
from pathlib import Path, PurePosixPath


PROJECT_ROOT = Path(__file__).resolve().parents[1]
DEFAULT_CONFIG = PROJECT_ROOT / "config" / "hosting.env"
DEFAULT_SECRETS = PROJECT_ROOT / ".secrets" / "hosting.env"
DEFAULT_OUTPUT = PROJECT_ROOT / ".private" / "hosting-tree.txt"


def read_env(path: Path) -> dict[str, str]:
    values: dict[str, str] = {}
    if not path.exists():
        return values
    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        values[key.strip()] = value.strip().strip('"').strip("'")
    return values


@dataclass(frozen=True)
class Entry:
    path: PurePosixPath
    is_dir: bool
    size: int | None = None


class InventoryError(RuntimeError):
    pass


HANDLED_ERRORS = (InventoryError, OSError) + ftplib.all_errors


def connect(config: dict[str, str], password: str) -> ftplib.FTP:
    host = config["FTP_HOST"]
    port = int(config.get("FTP_PORT", "21"))
    security = config.get("FTP_SECURITY", "ftps").lower()
    client: ftplib.FTP

    if security == "ftps":
        tls = ftplib.FTP_TLS(timeout=20)
        tls.connect(host, port)
        tls.login(config["FTP_USER"], password)
        tls.prot_p()
        client = tls
    elif security == "ftp":
        client = ftplib.FTP(timeout=20)
        client.connect(host, port)
        client.login(config["FTP_USER"], password)
    else:
        raise InventoryError("FTP_SECURITY는 ftps 또는 ftp만 사용할 수 있습니다.")

    client.set_pasv(True)
    return client


def list_directory(client: ftplib.FTP, path: PurePosixPath) -> list[Entry]:
    entries: list[Entry] = []
    try:
        for name, facts in client.mlsd(str(path), facts=["type", "size"]):
            if name in {".", ".."}:
                continue
            kind = facts.get("type", "")
            if kind in {"cdir", "pdir"}:
                continue
            size_text = facts.get("size")
            entries.append(
                Entry(
                    path=path / name,
                    is_dir=kind == "dir",
                    size=int(size_text) if size_text and size_text.isdigit() else None,
                )
            )
    except ftplib.error_perm:
        # 구형 카페24 FTP 서버는 MLSD를 제공하지 않으므로 Unix LIST를 읽는다.
        lines: list[str] = []
        previous = client.pwd()
        try:
            client.cwd(str(path))
            client.retrlines("LIST", lines.append)
        except ftplib.error_perm as exc:
            raise InventoryError(f"디렉터리 목록을 읽을 수 없습니다: {path}: {exc}") from exc
        finally:
            client.cwd(previous)

        for line in lines:
            parts = line.split(maxsplit=8)
            if len(parts) < 9:
                continue
            name = parts[8]
            if name in {".", ".."}:
                continue
            is_dir = parts[0].startswith("d")
            size_text = parts[4]
            entries.append(
                Entry(
                    path=path / name,
                    is_dir=is_dir,
                    size=int(size_text) if not is_dir and size_text.isdigit() else None,
                )
            )
    return sorted(entries, key=lambda item: (not item.is_dir, item.path.name.casefold()))


def collect_tree(client: ftplib.FTP, root: PurePosixPath, max_depth: int) -> list[Entry]:
    collected: list[Entry] = [Entry(root, True)]

    def visit(directory: PurePosixPath, depth: int) -> None:
        if depth >= max_depth:
            return
        for entry in list_directory(client, directory):
            collected.append(entry)
            if entry.is_dir:
                visit(entry.path, depth + 1)

    visit(root, 0)
    return collected


def human_size(size: int | None) -> str:
    if size is None:
        return ""
    units = ["B", "KB", "MB", "GB"]
    value = float(size)
    for unit in units:
        if value < 1024 or unit == units[-1]:
            return f"{value:.0f}{unit}" if unit == "B" else f"{value:.1f}{unit}"
        value /= 1024
    return ""


def render_tree(entries: list[Entry], root: PurePosixPath) -> str:
    lines = [f"{root} (remote)"]
    for entry in entries[1:]:
        try:
            relative = entry.path.relative_to(root)
        except ValueError:
            relative = entry.path
        depth = max(len(relative.parts) - 1, 0)
        marker = "[D]" if entry.is_dir else "[F]"
        size = "" if entry.is_dir else f"  {human_size(entry.size)}"
        lines.append(f"{'  ' * depth}{marker} {entry.path.name}{size}")
    return "\n".join(lines) + "\n"


def ensure_private_file(path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    os.chmod(path.parent, stat.S_IRWXU)


def main() -> int:
    parser = argparse.ArgumentParser(description="인투샵 FTP/FTPS 파일 트리 읽기")
    parser.add_argument("--max-depth", type=int, default=20)
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT)
    args = parser.parse_args()

    config = read_env(DEFAULT_CONFIG)
    secrets = read_env(DEFAULT_SECRETS)
    password = secrets.get("FTP_PASSWORD", "")

    required = [key for key in ("FTP_HOST", "FTP_USER") if not config.get(key)]
    if required:
        raise InventoryError(f"접속 설정 누락: {', '.join(required)}")
    if not password:
        raise InventoryError(
            ".secrets/hosting.env에 FTP_PASSWORD가 없습니다. "
            "config/hosting-secrets.env.example을 참고하세요."
        )

    output = args.output.resolve()
    private_root = (PROJECT_ROOT / ".private").resolve()
    if private_root not in output.parents and output != private_root:
        raise InventoryError("서버 목록은 .private 디렉터리 안에만 저장할 수 있습니다.")

    client: ftplib.FTP | None = None
    try:
        client = connect(config, password)
        root = PurePosixPath(config.get("FTP_ROOT", "/"))
        entries = collect_tree(client, root, max(args.max_depth, 1))
        ensure_private_file(output)
        output.write_text(render_tree(entries, root), encoding="utf-8")
        os.chmod(output, stat.S_IRUSR | stat.S_IWUSR)
        print(f"파일 트리를 로컬 비공개 경로에 저장했습니다: {output}")
        print(f"항목 수: {max(len(entries) - 1, 0)}")
        return 0
    finally:
        if client is not None:
            try:
                client.quit()
            except ftplib.all_errors:
                client.close()


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except HANDLED_ERRORS as exc:
        print(f"오류: {exc}", file=sys.stderr)
        raise SystemExit(1)
