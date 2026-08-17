#!/usr/bin/env python3
"""기존 첫 화면과 숨은 이음말을 추출하고 링크 상태를 검수한다."""

from __future__ import annotations

import html
import json
import re
import socket
import ssl
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from urllib.parse import quote, urlsplit, urlunsplit

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / ".private" / "remote-analysis" / "www"
REPORT = ROOT / ".private" / "legacy-link-audit.json"
USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/126 Safari/537.36"

LINK_RE = re.compile(r'<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>', re.I | re.S)
CASE_RE = re.compile(r'case\s+"([^"]+)"\s*:\s*location\.href\s*=\s*"([^"]+)"')


def clean_text(value: str) -> str:
    value = re.sub(r"<[^>]+>", " ", value)
    return " ".join(html.unescape(value).replace("\xa0", " ").split())


def links(fragment: str) -> list[dict[str, str]]:
    result = []
    for url, label in LINK_RE.findall(fragment):
        label = clean_text(label)
        if label and url.startswith(("http://", "https://")):
            result.append({"name": label, "url": html.unescape(url)})
    return result


def extract_html(source: str) -> list[dict[str, object]]:
    sections: list[dict[str, object]] = []

    welcome = source[source.find("마중말"):source.find("<!-- 광고단 끝 -->")]
    sections.append({"source": "html", "category": "마중말", "items": links(welcome)})

    left = source[source.find("<!-- 좌측 카테고리단 시작 -->"):source.find("<!-- 좌측 카테고리단 끝 -->")]
    blocks = re.split(r'(?=<div class="graybox">)', left)
    for block in blocks:
        title_match = re.search(r'<div class="categorybox"[^>]*>(.*?)</div>', block, re.I | re.S)
        if not title_match:
            continue
        title = clean_text(title_match.group(1)).lstrip("#")
        sections.append({"source": "html", "category": title, "items": links(block)})

    new_words = source[source.find("<!-- 새이음말 시작 -->"):source.find("<!-- 요사이 풀린말 시작 -->")]
    sections.append({"source": "html", "category": "새 이음말", "items": links(new_words)})

    released = source[source.find("<!-- 요사이 풀린말 시작 -->"):source.find("<!-- 우측 카테고리단 끝 -->")]
    released_names = [clean_text(item) for item in re.findall(r"<li>(.*?)</li>", released, re.I | re.S)]
    sections.append({
        "source": "html",
        "category": "풀린말",
        "items": [{"name": name, "url": ""} for name in released_names if name],
    })
    return sections


def extract_js(source: str) -> list[dict[str, object]]:
    sections: dict[str, list[dict[str, str]]] = {"검색 이음말": []}
    category = "숨은 이음말"
    in_direct_words = False
    for raw_line in source.splitlines():
        line = raw_line.strip()
        if line.startswith("//이음어 등록 시작"):
            in_direct_words = True
            continue
        if line.startswith("// 이음어 등록 끝"):
            in_direct_words = False
            continue
        if line.startswith("//"):
            heading = clean_text(line[2:]).strip()
            if in_direct_words and heading and heading not in {"이음어 등록 시작"}:
                category = heading
                sections.setdefault(category, [])
            continue
        match = CASE_RE.search(line)
        if not match:
            continue
        name, url = match.groups()
        url = url.split('" +', 1)[0]
        target = category if in_direct_words else "검색 이음말"
        sections.setdefault(target, []).append({"name": name, "url": url})
    return [{"source": "js", "category": category, "items": items} for category, items in sections.items() if items]


def normalized(url: str) -> str:
    parts = urlsplit(url)
    host = parts.netloc.casefold().removeprefix("www.")
    path = parts.path.rstrip("/") or "/"
    return urlunsplit((parts.scheme.casefold(), host, path, parts.query, ""))


def request(url: str) -> tuple[int | None, str, str]:
    encoded_url = quote(url, safe=":/?&=%#[]@!$'()*+,;~")
    request_object = urllib.request.Request(encoded_url, headers={"User-Agent": USER_AGENT})
    try:
        with urllib.request.urlopen(request_object, timeout=8, context=ssl.create_default_context()) as response:
            return response.status, response.geturl(), "ok"
    except urllib.error.HTTPError as error:
        state = "restricted" if error.code in {401, 403, 405, 406, 429} else "failed"
        return error.code, error.geturl(), state
    except (urllib.error.URLError, TimeoutError, socket.timeout, ssl.SSLError, UnicodeError, OSError) as error:
        return None, url, type(getattr(error, "reason", error)).__name__


def check_url(url: str) -> dict[str, object]:
    status, final_url, state = request(url)
    if status is None and url.startswith("http://"):
        secure_url = "https://" + url.removeprefix("http://")
        secure_status, secure_final, secure_state = request(secure_url)
        if secure_status is not None:
            return {"status": secure_status, "state": secure_state, "final_url": secure_final, "upgraded": True}
    return {"status": status, "state": state, "final_url": final_url, "upgraded": False}


def main() -> int:
    html_source = (SOURCE / "index.html").read_text(encoding="utf-8")
    js_source = (SOURCE / "index1.js").read_text(encoding="utf-8")
    sections = extract_html(html_source) + extract_js(js_source)

    unique_urls: dict[str, str] = {}
    for section in sections:
        for item in section["items"]:
            url = item["url"]
            if url:
                unique_urls.setdefault(normalized(url), url)

    results: dict[str, dict[str, object]] = {}
    with ThreadPoolExecutor(max_workers=12) as executor:
        futures = {executor.submit(check_url, url): key for key, url in unique_urls.items()}
        for future in as_completed(futures):
            results[futures[future]] = future.result()

    for section in sections:
        for item in section["items"]:
            url = item["url"]
            if url:
                item["check"] = results[normalized(url)]

    summary = {
        "section_count": len(sections),
        "entry_count": sum(len(section["items"]) for section in sections),
        "unique_url_count": len(unique_urls),
        "ok": sum(result["state"] == "ok" for result in results.values()),
        "restricted": sum(result["state"] == "restricted" for result in results.values()),
        "failed": sum(result["state"] not in {"ok", "restricted"} for result in results.values()),
    }
    REPORT.write_text(json.dumps({"summary": summary, "sections": sections}, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(summary, ensure_ascii=False))
    print(f"보고서: {REPORT}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
