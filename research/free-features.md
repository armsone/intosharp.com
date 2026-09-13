# 경쟁사 유료 기능의 무료 제공 — 선정 근거 (2026-09-13)

작성 기준: market-comparison.md, product-recommendations.md, 현행 소스(index.php, admin.js)를 읽은 뒤 당일 공식 요금·도움말 페이지를 다시 확인했다. 가격과 무료·유료 경계는 확인일(2026-09-13) 기준 표기이며 이후 바뀔 수 있다. 직접 열람하지 못한 페이지(Start.me)는 검색 결과 요약에 의존했으므로 원문과 다를 수 있다.

## 1. 공식 페이지(일부는 검색 결과 요약)에서 확인한 유료 경계

| 서비스 | 확인 URL | 확인한 문구 (유료) | 확인한 문구 (무료) |
|---|---|---|---|
| Abunch | https://abunch.io/ | Standard: "Daily + Occasional dual desktop", "Up to 200 categories and 2,000 links" | Free: "Up to 20 categories and 200 links", "A clean, ad-free homepage", "Cross-device sync", 단일 데스크톱 |
| Raindrop.io | https://help.raindrop.io/premium-features (검색 결과 https://raindrop.io/pro/buy 도 동일 취지) | Pro: "duplicate finder", "Broken link checker", "Reminders", "Full-text search", "Web archive", "Automatic backups"(scheduled exports) | "completely free with unlimited bookmarks, collections, and devices". 수동 내보내기·기본 검색은 유료로 표기되지 않음 |
| Toby | https://www.gettoby.com/pricing | Productivity: "Unlimited saved tabs", "Remove duplicates", 고급 검색 | Starter: "Up to 60 saved tabs", Notes·Tags·Collections·기본 검색 |
| Instapaper | https://www.instapaper.com/premium | Premium: "Unlimited notes"(무료는 월 5개 메모 제한), "Full-text search", "Permanent archive" | "Save unlimited articles", "Create folders" |
| Anybox | https://anybox.app/ | Pro: "Save unlimited links", Anydock 30개 | Free: "Save 50 links in total", Anydock 12개, "Export bookmarks effortlessly" |
| Momentum | https://momentumdash.com/plus | Plus: "Unlimited Task Lists", "Link Groups", Notes with AI, Tab Stash | 무료 범위는 해당 페이지에 명시되지 않음 |
| Start.me | https://support.start.me/en/articles/9182818-why-should-i-upgrade-my-free-account-to-pro (직접 열람 403, 검색 결과 요약으로 확인) | PRO: 페이지 수 무제한, 광고 제거, "broken and duplicate link detection" 등 고급 북마크 도구 | Free: 개인 페이지 3개 |

## 2. 이번에 무료로 구현한 기능군 ("내 이음말")

| 구분 | 기능 | 경쟁사 유료 근거 | 구현 방식 |
|---|---|---|---|
| 유료 대응 | 여러 개인 링크판(기본 매일·가끔·업무, 추가·이름 변경·삭제 자유) | Abunch Standard 이중 데스크톱, Start.me PRO 페이지 무제한 | localStorage `intosh-personal-v1`, 앱 자체의 판 수·링크 수 제한은 없음(브라우저 저장 용량 한도 안에서) |
| 유료 대응 | 링크별 한 줄 메모 + 다음 할 일(완료 체크) | Instapaper Premium 무제한 메모, Momentum Plus 다중 할 일 | 링크 항목 필드, 완료는 확인용 표시일 뿐 실제 사이트 업무 완료와 무관 |
| 유료 대응 | 중복 정리(같은 주소 찾아 선택 삭제, 되돌리기) | Raindrop Pro duplicate finder, Toby Productivity Remove duplicates | 정규화된 주소 전체(프로토콜·호스트·포트·경로·쿼리·해시)가 완전히 같을 때만 중복으로 봄. www 유무나 끝 슬래시만 다른 주소는 별개로 두어 오인 삭제를 피함. 판 전체 대상 |
| 기반(무료) | 검색·정렬(이름/추가순/최근 연 순/수동), 검색줄에서 개인 링크 이름 입력 시 바로 이동 | Raindrop·Toby 무료에도 기본 검색 있음. 유료 주장 아님 | 판 전체 검색, 정렬은 판별 저장 |
| 기반(무료) | 백업·복원(JSON, Netscape 북마크 HTML), 가져오기 미리보기 후 추가/교체 선택, 저장 용량·내보내기 실패 처리 | Raindrop Pro는 "자동" 백업만 유료. 수동 내보내기는 무료이므로 기반 기능 | 브라우저 다운로드, 실패 시 텍스트 복사 대체. 저장 원본을 읽지 못하면 덮어쓰지 않고 저장을 멈춘 뒤 원본 내보내기를 제공 |

## 3. 이번 범위에서 제외한 것(구현·주장하지 않음)

- **깨진 링크 점검**: 일반 웹페이지는 타 도메인 응답을 CORS 때문에 읽을 수 없다. 서버 점검이 필요하므로 후속 범위.
- **기기 간 동기화·협업·공유 URL**: 계정·서버 저장이 필요하다. product-recommendations.md P1-1의 URL 해시 공유는 미구현.
- **브라우저 방문 기록·열린 탭 수집**: 확장 권한 필요. "최근 연 순"은 인투샾 화면에서 클릭한 기록만 사용한다.
- **localStorage는 영구 백업이 아니다**: 사이트 데이터 삭제·브라우저 초기화·시크릿 모드 종료 시 사라진다. 화면에 이 사실과 백업 파일 보관 안내를 명시했다.
- 개인 링크는 서버로 보내지 않는다(개인 링크 데이터에 한정한 진술). 관리자 링크·검색 설정 동작은 기존과 같다.
