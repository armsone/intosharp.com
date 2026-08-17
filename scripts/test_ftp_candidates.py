#!/usr/bin/env python3
"""표준입력으로 받은 최대 3개의 후보를 FTPS로만 시험한다.

후보 값은 출력하지 않으며, 성공한 값만 Git 제외 비밀파일에 보관한다.
"""

from __future__ import annotations

import argparse
import ftplib
import os
import stat
import sys
import termios
from pathlib import Path

from ftp_inventory import DEFAULT_CONFIG, DEFAULT_SECRETS, connect, read_env


def save_password(password: str) -> None:
    DEFAULT_SECRETS.parent.mkdir(parents=True, exist_ok=True)
    os.chmod(DEFAULT_SECRETS.parent, stat.S_IRWXU)
    DEFAULT_SECRETS.write_text(f"FTP_PASSWORD={password}\n", encoding="utf-8")
    os.chmod(DEFAULT_SECRETS, stat.S_IRUSR | stat.S_IWUSR)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--count", type=int, choices=range(1, 4), default=3)
    args = parser.parse_args()

    config = read_env(DEFAULT_CONFIG)
    previous_terminal = None
    if sys.stdin.isatty():
        previous_terminal = termios.tcgetattr(sys.stdin.fileno())
        hidden_terminal = previous_terminal.copy()
        hidden_terminal[3] &= ~termios.ECHO
        termios.tcsetattr(sys.stdin.fileno(), termios.TCSADRAIN, hidden_terminal)
    try:
        candidates = [sys.stdin.readline().rstrip("\r\n") for _ in range(args.count)]
    finally:
        if previous_terminal is not None:
            termios.tcsetattr(sys.stdin.fileno(), termios.TCSADRAIN, previous_terminal)

    if any(not candidate for candidate in candidates):
        print(f"오류: 후보 {args.count}개를 모두 입력해야 합니다.", file=sys.stderr)
        return 1

    for index, candidate in enumerate(candidates, start=1):
        client: ftplib.FTP | None = None
        try:
            client = connect(config, candidate)
            client.pwd()
            save_password(candidate)
            print(f"후보 {index}: 접속 성공")
            return 0
        except ftplib.error_perm:
            print(f"후보 {index}: 인증 실패")
        except (ftplib.error_reply, ftplib.error_proto, OSError) as exc:
            print(f"FTP 연결을 사용할 수 없습니다: {type(exc).__name__}", file=sys.stderr)
            return 2
        finally:
            try:
                if client is not None:
                    client.close()
            except OSError:
                pass

    print("입력한 후보가 인증되지 않았습니다.", file=sys.stderr)
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
