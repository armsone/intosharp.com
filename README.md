# intoSharp start page

오래된 `intosharp.com` 시작 페이지를 웨일 북마크 기준으로 다시 만든 정적 웹사이트입니다.

## 로컬 실행

```sh
python3 -m http.server 4173
```

브라우저에서 `http://localhost:4173`을 엽니다.

## 검색 문법

- `네이버` → 네이버로 이동
- `네이버 우리집` → 네이버에서 “우리집” 검색
- `구글`, `유튜브`, `지도`, `쇼핑`도 같은 방식 지원
- 등록되지 않은 문장은 네이버에서 검색
- URL은 바로 이동

초기 정적 시안의 북마크 목록과 검색 동작은 `index.html`, `script.js`에 보존되어 있습니다.

현재 운영 첫 화면은 `index.php` 원본을 정적 `www/index.html`로 올리고, 관리자 편집은
`admin.js`와 `admin-api.php`가 담당합니다. 편집 데이터는 공개 경로에서 차단된
`.intosharp-data/site-state.json`에 저장되며 인증 입력 원문은 저장하지 않습니다.

## 카페24 호스팅 관리

카페24의 `intosharp` 호스팅 접속 메타데이터는 `config/hosting.env`에서 관리합니다.
비밀번호와 서버 조사 결과는 각각 `.secrets/`, `.private/`에만 저장하며 두 디렉터리는
외부 저장소에 포함되지 않도록 `.gitignore`에서 제외합니다.

### FTP 비밀번호 등록

```sh
mkdir -p .secrets
cp config/hosting-secrets.env.example .secrets/hosting.env
chmod 700 .secrets
chmod 600 .secrets/hosting.env
```

그다음 `.secrets/hosting.env`의 `FTP_PASSWORD`에 카페24 FTP/SSH 비밀번호를 입력합니다.
이 값을 채팅, 문서, 소스 코드 또는 커밋에 복사하지 마세요.

### 서버 파일 트리 조사

```sh
python3 scripts/ftp_inventory.py
```

도구는 서버에서 디렉터리 목록만 읽으며 파일을 내려받거나 수정·삭제하지 않습니다.
결과는 권한이 제한된 `.private/hosting-tree.txt`에 저장됩니다. 기본 연결은 비밀번호가
암호화되는 FTPS입니다. 서버가 FTPS를 지원하지 않을 경우에도 자동으로 평문 FTP로
낮추지 않습니다.
