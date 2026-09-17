<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f4f0e7">
  <meta name="color-scheme" content="light dark">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="description" content="주소 대신 이름으로 이어지는 인투샾 첫 화면">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="ko_KR">
  <meta property="og:site_name" content="인투샾">
  <meta property="og:title" content="#인투샾 — 이름으로 여는 첫 화면">
  <meta property="og:description" content="주소 대신 이름으로 이어지는 인투샾 첫 화면">
  <meta property="og:url" content="https://intosharp.com/">
  <meta property="og:image" content="https://intosharp.com/assets/intosharp-representative.png">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="코랄색 해시태그에서 검색과 바로가기 카드가 이어지는 인투샾 대표 이미지">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="#인투샾 — 이름으로 여는 첫 화면">
  <meta name="twitter:description" content="주소 대신 이름으로 이어지는 인투샾 첫 화면">
  <meta name="twitter:image" content="https://intosharp.com/assets/intosharp-representative.png">
  <link rel="canonical" href="https://intosharp.com/">
  <link rel="preload" href="/icon-192.webp" as="image" type="image/webp">
  <link rel="icon" href="/favicon-32.png?v=20260904" type="image/png" sizes="32x32">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260904" sizes="180x180">
  <link rel="manifest" href="/manifest.json?v=20260819-1">
  <title>#인투샾 — 이름으로 여는 첫 화면</title>
  <style>
    :root {
      color-scheme: light;
      --paper: #f4f0e7;
      --paper-deep: #eae4d8;
      --card: rgba(255, 253, 248, .86);
      --ink: #17211d;
      --muted: #6f756f;
      --line: rgba(23, 33, 29, .12);
      --accent: #ff6846;
      --accent-soft: #ffe1d8;
      --shadow: 0 18px 55px rgba(58, 49, 36, .09);
      --radius: 28px;
    }

    [data-theme="dark"] {
      color-scheme: dark;
      --paper: #111613;
      --paper-deep: #181f1b;
      --card: rgba(28, 36, 31, .84);
      --ink: #f4f0e7;
      --muted: #a8afa9;
      --line: rgba(255, 255, 255, .12);
      --accent: #ff795d;
      --accent-soft: #4b2922;
      --shadow: 0 18px 55px rgba(0, 0, 0, .24);
    }

    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    html.modern-ready { view-transition-name: none; }
    body {
      margin: 0;
      min-width: 320px;
      min-height: 100vh;
      color: var(--ink);
      background:
        radial-gradient(circle at 8% 0%, rgba(255, 104, 70, .12), transparent 27rem),
        radial-gradient(circle at 92% 24%, rgba(60, 145, 117, .10), transparent 24rem),
        var(--paper);
      font-family: Pretendard, -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", "Noto Sans KR", sans-serif;
      transition: color .2s ease, background-color .2s ease;
    }

    a { color: inherit; text-decoration: none; }
    button, input { font: inherit; }
    button { color: inherit; }
    :focus-visible { outline: 3px solid color-mix(in srgb, var(--accent) 72%, transparent); outline-offset: 3px; }

    .skip-link {
      position: fixed; top: -60px; left: 20px; z-index: 100;
      padding: 12px 16px; border-radius: 12px; background: var(--ink); color: var(--paper);
    }
    .skip-link:focus { top: 16px; }

    .topbar {
      width: min(1180px, calc(100% - 40px));
      height: 82px;
      margin: 0 auto;
      display: flex; align-items: center; justify-content: space-between;
    }

    .brand { display: inline-flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
    .brand-mark {
      width: 35px; height: 35px; display: grid; place-items: center;
      border-radius: 12px; color: #fff; background: var(--accent); font-size: 23px; line-height: 1;
      box-shadow: 0 8px 22px rgba(255, 104, 70, .25);
    }

    .top-actions { display: flex; align-items: center; gap: 12px; color: var(--muted); }
    #clock { min-width: 72px; text-align: right; font-size: 13px; font-variant-numeric: tabular-nums; }
    .theme-toggle {
      width: 40px; height: 40px; border: 1px solid var(--line); border-radius: 14px;
      display: grid; place-items: center; background: var(--card); cursor: pointer;
    }
    .theme-toggle:hover { border-color: var(--accent); }

    main { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
    .hero { padding: 70px 0 52px; text-align: center; }
    .kicker {
      margin: 0 0 18px; color: var(--accent); font-size: 12px; font-weight: 800;
      letter-spacing: .16em; text-transform: uppercase;
    }
    h1 { margin: 0; font-size: clamp(42px, 7vw, 82px); line-height: 1.02; letter-spacing: -.065em; }
    .hero-copy { margin: 20px auto 38px; color: var(--muted); font-size: clamp(16px, 2vw, 20px); line-height: 1.7; }

    .command {
      width: min(760px, 100%); min-height: 72px; margin: 0 auto;
      display: flex; align-items: center; gap: 14px; padding: 10px 12px 10px 22px;
      border: 1px solid var(--line); border-radius: 24px; background: var(--card);
      box-shadow: var(--shadow); backdrop-filter: blur(18px);
    }
    .command:focus-within { border-color: rgba(255, 104, 70, .72); box-shadow: 0 20px 60px rgba(255, 104, 70, .12); }
    .command-prefix { color: var(--accent); font-size: 28px; font-weight: 800; }
    .command input {
      min-width: 0; flex: 1; border: 0; outline: 0; color: var(--ink); background: transparent;
      font-size: clamp(17px, 2.3vw, 22px); letter-spacing: -.02em;
    }
    .command input::placeholder { color: color-mix(in srgb, var(--muted) 72%, transparent); }
    .go {
      width: 52px; height: 52px; flex: 0 0 auto; border: 0; border-radius: 17px;
      color: #fff; background: var(--accent); font-size: 22px; cursor: pointer;
      transition: transform .15s ease;
    }
    .go:hover { transform: translateY(-2px); }

    .command-help { margin: 15px 0 0; color: var(--muted); font-size: 13px; }
    .example {
      padding: 3px 7px; border: 0; border-radius: 7px; color: var(--ink); background: var(--paper-deep); cursor: pointer;
    }

    .welcome-words {
      display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 24px; align-items: center;
      padding: 22px 26px; border: 1px solid var(--line); border-radius: 24px;
      background: var(--ink); color: var(--paper); box-shadow: var(--shadow);
    }
    .welcome-words h2 { margin: 0; font-size: 20px; letter-spacing: -.035em; }
    .welcome-words p { margin: 6px 0 0; color: color-mix(in srgb, var(--paper) 66%, transparent); font-size: 11px; }
    .word-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .word-chip {
      padding: 8px 11px; border: 1px solid color-mix(in srgb, currentColor 18%, transparent);
      border-radius: 11px; background: color-mix(in srgb, currentColor 8%, transparent);
      font-size: 12px; transition: border-color .15s ease, transform .15s ease;
    }
    a.word-chip:hover { border-color: var(--accent); transform: translateY(-1px); }
    span.word-chip { color: var(--muted); text-decoration: line-through; }

    .combined-layout {
      display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 18px; align-items: start;
    }
    .combined-main { min-width: 0; }
    .side-words {
      display: grid; grid-template-columns: 1fr; gap: 18px; position: sticky; top: 18px;
    }
    .side-word-card {
      padding: 22px; border: 1px solid var(--line); border-radius: 24px; background: var(--card);
      box-shadow: 0 8px 30px rgba(58, 49, 36, .04);
    }
    .side-word-card h3 { margin: 1px 0 0; font-size: 17px; letter-spacing: -.025em; }
    .side-word-card p { margin: 6px 0 0; color: var(--muted); font-size: 11px; line-height: 1.45; }
    .side-word-card .word-chips { margin-top: 18px; }

    .search-panel {
      width: 100%; margin: 0 0 18px; padding: 20px;
      border: 1px solid var(--line); border-radius: 24px; background: var(--card);
      box-shadow: 0 8px 30px rgba(58, 49, 36, .04); text-align: left;
    }
    .search-panel[hidden] { display: none; }
    .search-panel-head { display: flex; align-items: baseline; justify-content: space-between; gap: 18px; margin-bottom: 14px; }
    .search-panel h2 { margin: 0; font-size: 15px; letter-spacing: -.02em; }
    .search-panel p { margin: 0; color: var(--muted); font-size: 12px; }
    .search-services { display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px; }
    .search-services button {
      display: flex; flex-direction: column; align-items: center; gap: 8px;
      padding: 11px 4px; border: 1px solid transparent; border-radius: 17px;
      color: var(--muted); background: transparent; font-size: 11px; cursor: pointer;
      transition: transform .16s ease, background .16s ease, color .16s ease, border-color .16s ease;
    }
    .search-services button:hover { transform: translateY(-2px); color: var(--ink); background: var(--paper-deep); }
    .search-services button.active { border-color: color-mix(in srgb, var(--accent) 45%, transparent); color: var(--ink); background: var(--accent-soft); }
    .quick-mark {
      width: 40px; height: 40px; display: grid; place-items: center; border-radius: 13px;
      color: #fff; font-weight: 800; font-size: 15px;
    }
    .quick-mark.green { background: #1ec800; }
    .quick-mark.red { background: #ff3131; }
    .quick-mark.dark { background: #202b26; }
    .quick-mark.blue { background: #4977e8; }
    .quick-mark.sky { background: #2aabee; }
    .quick-mark.indigo { background: #5963d9; }
    .quick-mark.yellow { background: #f1b400; }
    .quick-mark.cyan { background: #1683d8; }
    .quick-mark.orange { background: #f05b22; }

    .section-head {
      display: flex; align-items: end; justify-content: space-between; gap: 22px;
      margin: 54px 0 22px;
    }
    .section-head h2 { margin: 0; font-size: clamp(25px, 4vw, 38px); letter-spacing: -.045em; }
    .section-head p { margin: 8px 0 0; color: var(--muted); font-size: 14px; }
    .filters { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 2px; }
    .filters button {
      white-space: nowrap; border: 1px solid var(--line); border-radius: 999px;
      padding: 9px 14px; background: transparent; color: var(--muted); font-size: 13px; cursor: pointer;
    }
    .filters button.active { border-color: var(--ink); background: var(--ink); color: var(--paper); }

    .cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .card {
      min-width: 0;
      padding: 24px; border: 1px solid var(--line); border-radius: var(--radius);
      background: var(--card); box-shadow: 0 8px 30px rgba(58, 49, 36, .04);
      container-type: inline-size;
    }
    .card[hidden] { display: none; }
    .card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .card-title { display: flex; align-items: center; gap: 11px; }
    .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 5px var(--accent-soft); }
    .dot.green { background: #31a574; box-shadow: 0 0 0 5px rgba(49, 165, 116, .13); }
    .dot.blue { background: #4e82d8; box-shadow: 0 0 0 5px rgba(78, 130, 216, .13); }
    .dot.red { background: #e75b52; box-shadow: 0 0 0 5px rgba(231, 91, 82, .13); }
    .dot.violet { background: #866bd1; box-shadow: 0 0 0 5px rgba(134, 107, 209, .13); }
    .card h3 { margin: 0; font-size: 19px; letter-spacing: -.025em; }
    .card-head small { color: var(--muted); }
    .link-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
    .link-list a {
      min-width: 0; display: grid; grid-template-columns: 32px minmax(0, 1fr);
      align-items: center; gap: 6px; padding: 8px 6px; border-radius: 14px;
      transition: background .15s ease, transform .15s ease;
    }
    .link-list a:hover { background: var(--paper-deep); transform: translateX(2px); }
    .site-mark { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 10px; background: var(--paper-deep); font-size: 10px; font-weight: 800; overflow: hidden; }
    .site-mark.has-favicon { background: #fff; font-size: 0; }
    .quick-mark.has-favicon { background: #fff; font-size: 0; }
    .site-mark img, .quick-mark img { width: 22px; height: 22px; object-fit: contain; }
    .link-copy { min-width: 0; display: block; }
    .link-copy strong {
      display: block; overflow: visible; text-overflow: clip; white-space: normal;
      overflow-wrap: anywhere; word-break: keep-all; font-size: 13px; line-height: 1.3;
    }
    .link-copy small {
      display: block; margin-top: 2px; color: var(--muted); font-size: 10px; line-height: 1.35;
      white-space: nowrap; overflow: visible; text-overflow: clip; word-break: keep-all;
    }
    .arrow { display: none; }

    .archive { margin-top: 38px; }
    .archive-head { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 22px; }
    .archive-head h2 { margin: 0; font-size: clamp(25px, 4vw, 38px); letter-spacing: -.045em; }
    .archive-head p { margin: 8px 0 0; color: var(--muted); font-size: 14px; }
    .archive-count { flex: 0 0 auto; color: var(--muted); font-size: 12px; }
    .archive-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .archive-card { padding: 22px; border: 1px solid var(--line); border-radius: 24px; background: var(--card); }
    .archive-card.wide { grid-column: 1 / -1; }
    .archive-card header { margin-bottom: 15px; }
    .archive-card h3 { margin: 0; font-size: 17px; letter-spacing: -.02em; }
    .archive-card header p { margin: 5px 0 0; color: var(--muted); font-size: 11px; }
    .archive-chips { display: flex; flex-wrap: wrap; gap: 7px; }
    .archive-chip {
      padding: 8px 11px; border: 1px solid var(--line); border-radius: 11px;
      background: var(--paper-deep); color: var(--ink); font-size: 12px;
      transition: border-color .15s ease, transform .15s ease;
    }
    a.archive-chip:hover { border-color: var(--accent); transform: translateY(-1px); }
    span.archive-chip { color: var(--muted); text-decoration: line-through; }

    .legacy {
      margin: 72px 0 0; padding: 40px; border-radius: 34px; color: #f8f3e9;
      background: #19241f; display: grid; grid-template-columns: .85fr 1.15fr; gap: 44px;
    }
    .legacy h2 { margin: 0; font-size: clamp(28px, 4vw, 44px); line-height: 1.16; letter-spacing: -.045em; }
    .legacy h2 span { color: var(--accent); }
    .legacy-copy { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .legacy-copy article { padding-top: 13px; border-top: 1px solid rgba(255, 255, 255, .18); }
    .legacy-copy strong { display: block; margin-bottom: 7px; font-size: 14px; }
    .legacy-copy p { margin: 0; color: #acb6b0; font-size: 13px; line-height: 1.65; }

    footer {
      width: min(1180px, calc(100% - 40px)); margin: 0 auto; padding: 38px 0 42px;
      display: flex; justify-content: space-between; color: var(--muted); font-size: 12px;
    }
    .toast {
      position: fixed; left: 50%; bottom: 24px; z-index: 20; transform: translate(-50%, 20px);
      padding: 11px 16px; border-radius: 12px; color: var(--paper); background: var(--ink);
      opacity: 0; pointer-events: none; transition: .2s ease; font-size: 13px;
    }
    .toast.show { opacity: 1; transform: translate(-50%, 0); }

    .admin-account { display: flex; align-items: center; gap: 7px; padding-right: 2px; font-size: 12px; }
    .admin-account strong { color: var(--ink); font-size: 12px; }
    .admin-account button { padding: 5px 8px; border: 0; border-radius: 8px; color: var(--accent); background: var(--accent-soft); cursor: pointer; }
    .admin-login-button {
      padding: 6px 10px; border: 1px solid var(--line); border-radius: 9px;
      color: var(--muted); background: var(--card); font-size: 12px; cursor: pointer;
    }
    .login-dialog {
      width: min(390px, calc(100vw - 32px)); padding: 0; border: 1px solid var(--line);
      border-radius: 22px; color: var(--ink); background: var(--card); box-shadow: 0 24px 80px rgba(24, 33, 29, .22);
    }
    .login-dialog::backdrop { background: rgba(18, 25, 22, .46); backdrop-filter: blur(4px); }
    .login-form { display: grid; gap: 15px; padding: 26px; }
    .login-form h2 { margin: 0; font-size: 22px; }
    .login-form p { margin: -7px 0 2px; color: var(--muted); font-size: 12px; }
    .login-form label { display: grid; gap: 6px; color: var(--muted); font-size: 12px; }
    .login-form input {
      width: 100%; padding: 12px 13px; border: 1px solid var(--line); border-radius: 11px;
      color: var(--ink); background: var(--paper); font: inherit; outline: none;
    }
    .login-form input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
    .login-error { min-height: 17px; margin: 0 !important; color: #c43f38 !important; }
    .login-actions { display: flex; justify-content: flex-end; gap: 8px; }
    .login-actions button { padding: 9px 13px; border: 1px solid var(--line); border-radius: 10px; background: transparent; cursor: pointer; }
    .login-actions button[type="submit"] { color: #fff; border-color: var(--accent); background: var(--accent); }
    .admin-controls { display: flex; align-items: center; gap: 5px; }
    .admin-control {
      min-width: 28px; min-height: 28px; padding: 4px 8px; border: 1px solid var(--line); border-radius: 9px;
      color: var(--muted); background: var(--paper-deep); font-size: 12px; cursor: pointer;
    }
    .admin-control:hover { border-color: var(--accent); color: var(--ink); }
    .admin-control:disabled { opacity: .35; cursor: default; }
    .admin-control.admin-primary { padding-inline: 12px; color: #fff; border-color: var(--accent); background: var(--accent); }
    .admin-control.admin-danger { color: #fff; border-color: #d94c45; border-radius: 999px; background: #d94c45; font-size: 17px; line-height: 1; }
    .admin-control.admin-star { border-radius: 999px; font-size: 15px; }
    .admin-control.admin-star.active { color: #7a5800; border-color: #f1b400; background: #ffd85a; }
    .admin-mode .card { position: relative; }
    .admin-mode .card-head { min-height: 36px; }
    .group-controls { justify-content: flex-end; margin-bottom: 10px; }
    .group-controls .admin-control { width: 28px; padding-inline: 0; }
    .admin-drag-handle {
      width: 44px; min-width: 44px; height: 44px; display: grid; place-items: center; padding: 0;
      border: 1px solid var(--line); border-radius: 10px; color: var(--muted); background: var(--paper-deep);
      font-size: 16px; line-height: 1; cursor: grab; touch-action: none; user-select: none;
    }
    .admin-drag-handle:active { cursor: grabbing; }
    .admin-drag-handle:focus-visible { outline: 3px solid var(--accent-soft); border-color: var(--accent); }
    .group-title-edit { padding: 2px 4px; border: 0; border-radius: 7px; color: inherit; background: transparent; font-weight: 800; cursor: pointer; }
    .group-title-edit:hover { color: var(--accent); background: var(--accent-soft); }
    .admin-site-row {
      min-width: 0; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 4px;
      padding: 3px 4px 3px 6px; border: 1px solid var(--line); border-radius: 14px;
    }
    .admin-site-row > a { min-width: 0; grid-template-columns: minmax(0, 1fr); padding: 6px 5px; pointer-events: none; }
    .admin-site-row > a:hover { outline: 1px solid color-mix(in srgb, var(--accent) 35%, transparent); }
    .admin-site-row .site-mark { display: none; }
    .site-controls { align-self: center; display: grid; grid-template-columns: repeat(2, 28px); gap: 4px; padding: 0; }
    .site-controls .admin-control { width: 28px; min-width: 28px; min-height: 28px; height: 28px; padding: 0; font-size: 12px; }
    .site-controls .admin-danger { font-size: 14px; }
    .site-controls .admin-star { font-size: 11px; }
    .admin-mode [aria-grabbed="true"] { opacity: .55; }
    .admin-drop-target { outline: 2px dashed var(--accent); outline-offset: 3px; }

    .personal { margin-top: 18px; padding: 22px 24px; border: 1px solid var(--line); border-radius: 24px; background: var(--card); box-shadow: 0 8px 30px rgba(58, 49, 36, .04); }
    .personal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
    .personal-head h2 { margin: 0; font-size: 20px; letter-spacing: -.035em; }
    .personal-head p { margin: 6px 0 0; color: var(--muted); font-size: 12px; line-height: 1.5; }
    .personal-actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .personal-actions .admin-control { min-height: 32px; padding-inline: 11px; }
    .personal-tabs { display: flex; gap: 6px; overflow-x: auto; margin: 16px 0 10px; padding-bottom: 2px; }
    .personal-tabs button {
      white-space: nowrap; border: 1px solid var(--line); border-radius: 999px;
      padding: 8px 13px; background: transparent; color: var(--muted); font-size: 13px; cursor: pointer;
    }
    .personal-tabs button.active { border-color: var(--ink); background: var(--ink); color: var(--paper); }
    .personal-tools { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 8px; align-items: center; margin-bottom: 12px; }
    .personal-tools input[type="search"], .personal-tools select {
      min-width: 0; padding: 9px 12px; border: 1px solid var(--line); border-radius: 11px;
      color: var(--ink); background: var(--paper); font-size: 13px; outline: none;
    }
    .personal-tools input[type="search"]:focus, .personal-tools select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
    .personal-count { color: var(--muted); font-size: 12px; white-space: nowrap; }
    .personal-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
    .personal-row {
      min-width: 0; display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 4px;
      padding: 3px 4px 3px 2px; border: 1px solid var(--line); border-radius: 14px;
    }
    .personal-row > a {
      min-width: 0; display: grid; grid-template-columns: 32px minmax(0, 1fr); align-items: center; gap: 6px;
      padding: 6px 6px; border-radius: 12px; transition: background .15s ease;
    }
    .personal-row > a:hover { background: var(--paper-deep); }
    .personal-todo {
      grid-column: 1 / -1; display: flex; align-items: flex-start; gap: 7px; margin: 0 6px 6px 8px;
      color: var(--ink); font-size: 12px; line-height: 1.4; cursor: pointer;
    }
    .personal-todo input { margin: 2px 0 0; accent-color: var(--accent); }
    .personal-todo.done span { color: var(--muted); text-decoration: line-through; }
    .personal-controls { display: grid; grid-auto-flow: column; gap: 4px; }
    .personal-controls .admin-control { width: 28px; min-width: 28px; height: 28px; min-height: 28px; padding: 0; font-size: 12px; }
    .personal-controls .admin-danger { font-size: 14px; }
    .personal-empty { margin: 4px 0 0; padding: 18px; border: 1px dashed var(--line); border-radius: 14px; color: var(--muted); font-size: 13px; text-align: center; }
    .personal-empty[hidden] { display: none; }
    .personal-note { margin: 14px 0 0; color: var(--muted); font-size: 11px; line-height: 1.5; }
    .personal-dialog { width: min(520px, calc(100vw - 32px)); max-height: calc(100vh - 32px); overflow: auto; }
    .personal-dialog .login-form textarea { width: 100%; padding: 12px 13px; border: 1px solid var(--line); border-radius: 11px; color: var(--ink); background: var(--paper); font: inherit; outline: none; resize: vertical; }
    .personal-dialog .login-form textarea:focus, .personal-dialog .login-form select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
    .personal-dialog .login-form select { width: 100%; padding: 12px 13px; border: 1px solid var(--line); border-radius: 11px; color: var(--ink); background: var(--paper); font: inherit; outline: none; }
    .personal-dialog-body { display: grid; gap: 14px; }
    .personal-dialog-note { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.55; }
    .personal-dialog-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .personal-dialog-row .admin-control { min-height: 34px; padding-inline: 12px; }
    .personal-dialog-danger { padding-top: 10px; border-top: 1px solid var(--line); }
    .personal-file { display: grid; gap: 8px; color: var(--muted); font-size: 12px; }
    .personal-file input { font-size: 13px; color: var(--ink); }
    .personal-preview { display: grid; gap: 8px; padding: 14px; border: 1px solid var(--line); border-radius: 14px; background: var(--paper); }
    .personal-preview h3 { margin: 0; font-size: 14px; }
    .personal-preview p { margin: 0; color: var(--muted); font-size: 12px; }
    .personal-preview ul { margin: 0; padding-left: 18px; font-size: 12px; line-height: 1.5; }
    .personal-fallback { display: grid; gap: 8px; }
    .personal-fallback p { margin: 0; color: var(--muted); font-size: 12px; }
    .personal-fallback textarea { width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 11px; color: var(--ink); background: var(--paper); font-size: 11px; font-family: ui-monospace, monospace; }
    .personal-dupe { display: grid; gap: 6px; margin: 0 0 10px; padding: 10px 12px 12px; border: 1px solid var(--line); border-radius: 12px; }
    .personal-dupe legend { padding: 0 4px; color: var(--muted); font-size: 11px; overflow-wrap: anywhere; }
    .personal-dupe label { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; line-height: 1.4; cursor: pointer; }
    .personal-dupe input { margin-top: 3px; accent-color: var(--accent); }
    .personal-undo {
      position: fixed; left: 50%; bottom: 24px; z-index: 21; transform: translateX(-50%);
      display: flex; align-items: center; gap: 12px; padding: 10px 12px 10px 16px; border-radius: 12px;
      color: var(--paper); background: var(--ink); font-size: 13px; box-shadow: var(--shadow);
    }
    .personal-undo[hidden] { display: none; }
    .personal-undo button { padding: 6px 10px; border: 0; border-radius: 8px; color: var(--ink); background: var(--paper); font-weight: 700; cursor: pointer; }

    @supports (content-visibility: auto) {
      .card, .archive-card, .legacy { content-visibility: auto; contain-intrinsic-size: auto 360px; }
    }

    @view-transition { navigation: auto; }
    ::view-transition-old(root), ::view-transition-new(root) { animation-duration: .18s; }

    .top-nav { display: flex; gap: 24px; margin-left: auto; margin-right: 28px; color: var(--muted); font-size: 12px; }
    .top-nav a { position: relative; padding: 5px 0; }
    .top-nav a::after { content: ''; position: absolute; left: 0; right: 100%; bottom: 0; height: 1px; background: var(--accent); transition: right .2s ease; }
    .top-nav a:hover { color: var(--ink); }
    .top-nav a:hover::after { right: 0; }
    .hero { padding-top: 84px; text-align: left; }
    .hero h1 { font-size: clamp(48px, 8vw, 94px); font-weight: 500; }
    .hero h1 em { color: var(--accent); font-style: normal; }
    .hero-copy { margin-left: 0; }
    .command { margin-left: 0; }
    .command-help { text-align: left; }
    .hero-note { display: flex; align-items: center; gap: 9px; margin-top: 34px; color: var(--muted); font-size: 11px; letter-spacing: .02em; }
    .hero-note span { color: var(--accent); font-variant-numeric: tabular-nums; }
    .hero-note b { color: var(--ink); font-weight: 700; }
    .section-head { border-top: 1px solid var(--line); padding-top: 30px; }
    .section-head h2, .archive-head h2 { font-weight: 500; }
    .cards { counter-reset: catalog; }
    .card { position: relative; overflow: hidden; border-radius: 8px; }
    .card::before { counter-increment: catalog; content: '0' counter(catalog); position: absolute; top: 18px; right: 22px; color: var(--muted); font-size: 10px; letter-spacing: .12em; }
    .card:hover { border-color: color-mix(in srgb, var(--accent) 58%, var(--line)); box-shadow: 0 16px 38px rgba(58, 49, 36, .09); }
    .card-head { padding-right: 34px; }
    .link-list a { border-radius: 8px; }
    .filters button { border: 1px solid transparent; border-radius: 999px; }
    .filters button.active { border-color: var(--ink); }
    @media (max-width: 620px) {
      .top-nav { display: none; }
      .hero { padding-top: 54px; }
      .hero-note { margin-top: 26px; }
    }

    @media (max-width: 1000px) {
      .combined-layout { grid-template-columns: 1fr; }
      .side-words { grid-template-columns: repeat(2, minmax(0, 1fr)); position: static; }
    }
    @media (max-width: 850px) {
      .hero { padding-top: 48px; }
      .cards { grid-template-columns: 1fr; }
      .archive-grid { grid-template-columns: 1fr; }
      .archive-card.wide { grid-column: auto; }
      .legacy { grid-template-columns: 1fr; }
    }
    @media (max-width: 620px) {
      .topbar, main, footer { width: min(100% - 28px, 1180px); }
      .topbar { height: 68px; }
      #clock { display: none; }
      .hero { padding: 43px 0 36px; }
      .hero-copy { margin-bottom: 28px; }
      .welcome-words { grid-template-columns: 1fr; gap: 14px; padding: 20px; }
      .side-words { grid-template-columns: 1fr; }
      .command { min-height: 64px; padding-left: 17px; border-radius: 20px; }
      .go { width: 46px; height: 46px; border-radius: 15px; }
      .search-panel { padding: 16px 12px; }
      .search-panel-head { align-items: flex-start; flex-direction: column; gap: 5px; padding: 0 4px; }
      .search-services { grid-template-columns: repeat(4, 1fr); gap: 4px; }
      .section-head { align-items: flex-start; flex-direction: column; margin-top: 42px; }
      .filters { width: 100%; }
      .card { padding: 18px; }
      .link-list { grid-template-columns: 1fr; }
      .legacy { padding: 28px 22px; gap: 30px; }
      .legacy-copy { grid-template-columns: 1fr; }
      .archive-head { align-items: flex-start; flex-direction: column; }
      .admin-account strong { display: none; }
      .admin-account { gap: 3px; }
      .admin-site-row { grid-template-columns: auto minmax(0, 1fr) auto; }
      .personal { padding: 18px 14px; }
      .personal-tools { grid-template-columns: 1fr; }
      .personal-count { text-align: right; }
      .personal-list { grid-template-columns: 1fr; }
      .personal-undo { width: calc(100% - 28px); justify-content: space-between; }
      footer { gap: 12px; flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { scroll-behavior: auto !important; transition: none !important; }
    }
  </style>
</head>
<body>
  <a class="skip-link" href="#bookmarks">바로가기로 건너뛰기</a>

  <header class="topbar">
    <a class="brand" href="./" aria-label="인투샾 처음으로">
      <img class="brand-mark" src="/icon-192.webp" width="35" height="35" alt="인투샾 아이콘" loading="lazy" decoding="async">
      <span>인투샾</span>
    </a>
    <nav class="top-nav" aria-label="페이지 이동">
      <a href="#bookmarks">이음말</a>
      <a href="#personal">내 링크</a>
      <a href="#legacyTitle">만든 뜻</a>
    </nav>
    <div class="top-actions">
      <button class="admin-login-button" id="adminLoginButton" type="button">관리자 로그인</button>
      <time id="clock" aria-label="현재 시각"></time>
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="어두운 화면으로 전환">◐</button>
    </div>
  </header>

  <main>
    <section class="hero" aria-labelledby="mainTitle">
      <p class="kicker">PERSONAL LINK CATALOG · 2026</p>
      <h1 id="mainTitle">자주 가는 곳을<br><em>한눈에.</em></h1>
      <p class="hero-copy">주소 대신 이름을 고르고, 필요한 곳으로 바로 이동하세요.</p>

      <form class="command" id="commandForm" role="search" autocomplete="off">
        <span class="command-prefix" aria-hidden="true">#</span>
        <label for="commandInput" style="position:absolute;clip:rect(0 0 0 0);clip-path:inset(50%);width:1px;height:1px;overflow:hidden">사이트 이름 또는 검색어</label>
        <input id="commandInput" type="search" inputmode="search" enterkeyhint="go" autofocus autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" placeholder="네이버 또는 네이버 우리집">
        <button class="go" type="submit" aria-label="이동">→</button>
      </form>
      <p class="command-help">
        <button class="example" type="button" data-example="네이버">네이버</button>는 바로 이동,
        <button class="example" type="button" data-example="네이버 우리집">네이버 우리집</button>은 네이버에서 검색
      </p>

      <div class="hero-note" aria-label="인투샾 사용 안내">
        <span>01</span><b>찾고</b><span>02</span><b>고르고</b><span>03</span><b>바로 갑니다</b>
      </div>

    </section>

    <section class="welcome-words" aria-labelledby="welcomeWordsTitle">
      <div>
        <h2 id="welcomeWordsTitle">마중말</h2>
        <p>첫 화면에서 먼저 만나는 이음말</p>
      </div>
      <div class="word-chips" id="welcomeWords"></div>
    </section>

    <section class="personal" id="personal" aria-labelledby="personalTitle">
      <div class="personal-head">
        <div>
          <h2 id="personalTitle">내 이음말</h2>
          <p>로그인 없이 내가 직접 넣는 링크판입니다. 매일·가끔·업무처럼 판을 나누고, 링크마다 메모와 다음 할 일을 적어 둡니다. 검색줄에 링크 이름을 치면 바로 열립니다.</p>
        </div>
        <div class="personal-actions">
          <button class="admin-control admin-primary" id="personalAddLink" type="button">＋ 링크 추가</button>
          <button class="admin-control" id="personalAddBoard" type="button">판 추가</button>
          <button class="admin-control" id="personalRenameBoard" type="button">판 이름</button>
          <button class="admin-control" id="personalDeleteBoard" type="button">판 삭제</button>
          <button class="admin-control" id="personalDedupe" type="button">중복 정리</button>
          <button class="admin-control" id="personalBackup" type="button">백업·복원</button>
        </div>
      </div>
      <div class="personal-tabs" id="personalTabs" role="tablist" aria-label="내 이음말 판 선택"></div>
      <div class="personal-tools">
        <label for="personalSearch" style="position:absolute;clip:rect(0 0 0 0);clip-path:inset(50%);width:1px;height:1px;overflow:hidden">내 이음말 검색</label>
        <input id="personalSearch" type="search" placeholder="모든 판에서 이름·주소·메모 검색" autocomplete="off">
        <label for="personalSort" style="position:absolute;clip:rect(0 0 0 0);clip-path:inset(50%);width:1px;height:1px;overflow:hidden">정렬</label>
        <select id="personalSort">
          <option value="manual">내 순서</option>
          <option value="name">이름순</option>
          <option value="added">추가순</option>
          <option value="opened">최근 연 순</option>
        </select>
        <span class="personal-count" id="personalCount" aria-live="polite"></span>
      </div>
      <div class="personal-list" id="personalList"></div>
      <p class="personal-empty" id="personalEmpty" hidden></p>
      <p class="personal-note">이 브라우저에만 저장되며 서버로 보내지 않습니다. 사이트 데이터를 지우면 함께 사라지므로 백업·복원에서 파일로 내보내 두세요. 다른 기기와 자동으로 맞춰지지 않습니다.</p>
    </section>

    <section id="bookmarks" aria-labelledby="bookmarksTitle">
      <div class="section-head">
        <div>
          <h2 id="bookmarksTitle">이음말</h2>
          <p>이름 하나로 이어지는 나의 자주 가는 곳입니다.</p>
        </div>
        <div class="filters" role="group" aria-label="바로가기 분류">
          <button class="active" type="button" data-filter="all" aria-pressed="true">전체</button>
          <button type="button" data-filter="group-search" aria-pressed="false">찾기</button>
          <button type="button" data-filter="group-0" aria-pressed="false">일</button>
          <button type="button" data-filter="group-1" aria-pressed="false">한통</button>
          <button type="button" data-filter="group-2" aria-pressed="false">이야기마당</button>
          <button type="button" data-filter="group-3" aria-pressed="false">볼거리</button>
          <button type="button" data-filter="group-4" aria-pressed="false">연장</button>
          <button type="button" data-filter="group-5" aria-pressed="false">패밀리</button>
          <button type="button" data-filter="group-6" aria-pressed="false">살거리</button>
          <button type="button" data-filter="group-7" aria-pressed="false">들머리</button>
          <button type="button" data-filter="group-8" aria-pressed="false">쉼</button>
          <button type="button" data-filter="group-9" aria-pressed="false">믿음</button>
        </div>
      </div>

      <div class="combined-layout">
        <div class="combined-main">
        <section class="search-panel" data-group="group-search" aria-labelledby="searchServicesTitle">
        <div class="search-panel-head">
          <h2 id="searchServicesTitle">찾는 곳</h2>
          <p><strong id="currentSearchService">네이버</strong> 선택 중 · 검색어만 입력해도 위 검색줄에서 찾습니다.</p>
        </div>
        <div class="search-services" role="group" aria-label="찾는 곳 선택">
          <button class="active" type="button" data-search-service="네이버" aria-pressed="true"><span class="quick-mark green">N</span><span>네이버</span></button>
          <button type="button" data-search-service="구글" aria-pressed="false"><span class="quick-mark blue">G</span><span>구글</span></button>
          <button type="button" data-search-service="다음" aria-pressed="false"><span class="quick-mark sky">D</span><span>다음</span></button>
          <button type="button" data-search-service="Bing" aria-pressed="false"><span class="quick-mark cyan">B</span><span>Bing</span></button>
          <button type="button" data-search-service="유튜브" aria-pressed="false"><span class="quick-mark red">▶</span><span>유튜브</span></button>
          <button type="button" data-search-service="쇼핑" aria-pressed="false"><span class="quick-mark yellow">쇼</span><span>쇼핑</span></button>
          <button type="button" data-search-service="지도" aria-pressed="false"><span class="quick-mark indigo">⌖</span><span>지도</span></button>
          <button type="button" data-search-service="쿠팡" aria-pressed="false"><span class="quick-mark orange">C</span><span>쿠팡</span></button>
        </div>
        </section>

        <div class="cards">
        <article class="card" data-group="group-0">
          <header class="card-head"><div class="card-title"><span class="dot"></span><h3>일</h3></div><small>5곳</small></header>
          <div class="link-list" id="workLinks">
            <a href="https://sell.smartstore.naver.com/"><span class="site-mark">SS</span><span class="link-copy"><strong>스마트스토어센터</strong><small>상품 · 주문</small></span><span class="arrow">↗</span></a>
            <a href="https://partner.talk.naver.com/"><span class="site-mark">톡</span><span class="link-copy"><strong>네이버 톡톡</strong><small>고객 상담</small></span><span class="arrow">↗</span></a>
            <a href="https://payapp.kr/"><span class="site-mark">PA</span><span class="link-copy"><strong>페이앱</strong><small>결제 관리</small></span><span class="arrow">↗</span></a>
            <a href="https://unipost.co.kr/"><span class="site-mark">UP</span><span class="link-copy"><strong>유니포스트</strong><small>전자증빙</small></span><span class="arrow">↗</span></a>
            <a href="https://members.iparking.co.kr/"><span class="site-mark">P</span><span class="link-copy"><strong>아이파킹</strong><small>주차 관리</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-1">
          <header class="card-head"><div class="card-title"><span class="dot green"></span><h3>한통</h3></div><small>5곳</small></header>
          <div class="link-list" id="hantongLinks">
            <a href="https://smartstore.naver.com/hantongbox"><span class="site-mark">한</span><span class="link-copy"><strong>한통 스토어</strong><small>스마트스토어</small></span><span class="arrow">↗</span></a>
            <a href="https://blog.naver.com/hantongbox"><span class="site-mark">B</span><span class="link-copy"><strong>한통 블로그</strong><small>소식과 이야기</small></span><span class="arrow">↗</span></a>
            <a href="https://www.instagram.com/hantongbox/"><span class="site-mark">IG</span><span class="link-copy"><strong>Instagram</strong><small>@hantongbox</small></span><span class="arrow">↗</span></a>
            <a href="https://www.youtube.com/@hantongbox/"><span class="site-mark">YT</span><span class="link-copy"><strong>YouTube</strong><small>영상 채널</small></span><span class="arrow">↗</span></a>
            <a href="https://tv.naver.com/hantongbox"><span class="site-mark">TV</span><span class="link-copy"><strong>네이버TV</strong><small>영상 채널</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-2">
          <header class="card-head"><div class="card-title"><span class="dot blue"></span><h3>이야기마당</h3></div><small>6곳</small></header>
          <div class="link-list" id="communityLinks">
            <a href="https://www.clien.net/service/"><span class="site-mark">C</span><span class="link-copy"><strong>클리앙</strong><small>IT · 생활</small></span><span class="arrow">↗</span></a>
            <a href="https://damoang.net/"><span class="site-mark">다</span><span class="link-copy"><strong>다모앙</strong><small>사람과 이야기</small></span><span class="arrow">↗</span></a>
            <a href="https://www.ppomppu.co.kr/"><span class="site-mark">P</span><span class="link-copy"><strong>뽐뿌</strong><small>정보 · 쇼핑</small></span><span class="arrow">↗</span></a>
            <a href="https://ruliweb.com/"><span class="site-mark">R</span><span class="link-copy"><strong>루리웹</strong><small>게임 · 취미</small></span><span class="arrow">↗</span></a>
            <a href="https://kmug.co.kr/"><span class="site-mark">K</span><span class="link-copy"><strong>KMUG</strong><small>Apple 커뮤니티</small></span><span class="arrow">↗</span></a>
            <a href="https://www.reddit.com/"><span class="site-mark">RD</span><span class="link-copy"><strong>Reddit</strong><small>세계 커뮤니티</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-3">
          <header class="card-head"><div class="card-title"><span class="dot red"></span><h3>볼거리</h3></div><small>6곳</small></header>
          <div class="link-list" id="mediaLinks">
            <a href="https://www.youtube.com/?gl=KR"><span class="site-mark">YT</span><span class="link-copy"><strong>YouTube</strong><small>영상</small></span><span class="arrow">↗</span></a>
            <a href="https://www.netflix.com/kr/"><span class="site-mark">N</span><span class="link-copy"><strong>Netflix</strong><small>영화와 시리즈</small></span><span class="arrow">↗</span></a>
            <a href="https://www.tving.com/"><span class="site-mark">TV</span><span class="link-copy"><strong>TVING</strong><small>방송 · 영화</small></span><span class="arrow">↗</span></a>
            <a href="https://www.wavve.com/"><span class="site-mark">W</span><span class="link-copy"><strong>Wavve</strong><small>방송 · 영화</small></span><span class="arrow">↗</span></a>
            <a href="https://www.coupangplay.com/"><span class="site-mark">CP</span><span class="link-copy"><strong>쿠팡플레이</strong><small>스포츠 · 시리즈</small></span><span class="arrow">↗</span></a>
            <a href="https://www.justwatch.com/kr"><span class="site-mark">JW</span><span class="link-copy"><strong>JustWatch</strong><small>스트리밍 통합 검색</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-4">
          <header class="card-head"><div class="card-title"><span class="dot violet"></span><h3>연장</h3></div><small>6곳</small></header>
          <div class="link-list" id="toolLinks">
            <a href="https://chatgpt.com/"><span class="site-mark">AI</span><span class="link-copy"><strong>ChatGPT</strong><small>대화형 AI</small></span><span class="arrow">↗</span></a>
            <a href="https://gemini.google.com/"><span class="site-mark">✦</span><span class="link-copy"><strong>Gemini</strong><small>Google AI</small></span><span class="arrow">↗</span></a>
            <a href="https://github.com/"><span class="site-mark">GH</span><span class="link-copy"><strong>GitHub</strong><small>코드와 프로젝트</small></span><span class="arrow">↗</span></a>
            <a href="https://appstoreconnect.apple.com/"><span class="site-mark">AS</span><span class="link-copy"><strong>App Store Connect</strong><small>앱 관리</small></span><span class="arrow">↗</span></a>
            <a href="https://www.icloud.com/"><span class="site-mark">iC</span><span class="link-copy"><strong>iCloud</strong><small>Apple 클라우드</small></span><span class="arrow">↗</span></a>
            <a href="https://www.google.com/"><span class="site-mark">G</span><span class="link-copy"><strong>Google</strong><small>웹 검색</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-5">
          <header class="card-head"><div class="card-title"><span class="dot green"></span><h3>패밀리 사이트</h3></div><small>3곳</small></header>
          <div class="link-list" id="familyLinks">
            <a href="https://intosharp.com/"><span class="site-mark">#</span><span class="link-copy"><strong>인투샾</strong><small>이름으로 여는 첫 화면</small></span><span class="arrow">↗</span></a>
            <a href="https://nasfinder.com/"><span class="site-mark">NF</span><span class="link-copy"><strong>나스파인더</strong><small>만든 앱과 소식</small></span><span class="arrow">↗</span></a>
            <a href="https://github.com/armsone"><span class="site-mark">GH</span><span class="link-copy"><strong>armsone GitHub</strong><small>코드와 공개 작업</small></span><span class="arrow">↗</span></a>
          </div>
        </article>
        <article class="card" data-group="group-6">
          <header class="card-head"><div class="card-title"><span class="dot"></span><h3>살거리</h3></div><small>0곳</small></header>
          <div class="link-list" id="shoppingLinks"></div>
        </article>
        <article class="card" data-group="group-7">
          <header class="card-head"><div class="card-title"><span class="dot blue"></span><h3>들머리</h3></div><small>0곳</small></header>
          <div class="link-list" id="portalLinks"></div>
        </article>
        <article class="card" data-group="group-8">
          <header class="card-head"><div class="card-title"><span class="dot red"></span><h3>쉼</h3></div><small>0곳</small></header>
          <div class="link-list" id="leisureLinks"></div>
        </article>
        <article class="card" data-group="group-9">
          <header class="card-head"><div class="card-title"><span class="dot violet"></span><h3>믿음</h3></div><small>0곳</small></header>
          <div class="link-list" id="faithLinks"></div>
        </article>
        </div>

        <section class="archive" data-group="archive" aria-labelledby="archiveTitle">
          <div class="archive-head">
            <div>
              <h2 id="archiveTitle">이어온 이음말</h2>
              <p>알맞은 분류로 옮기고, 겹치는 항목을 걷어낸 뒤 남은 것만 한데 모았습니다.</p>
            </div>
            <span class="archive-count" id="archiveCount"></span>
          </div>
          <div class="archive-grid" id="archiveGrid"></div>
        </section>
        </div>

        <aside class="side-words" aria-label="새 이음말과 풀린말">
          <article class="side-word-card">
            <div><h3>새 이음말</h3><p>새로 이어 놓은 이름</p></div>
            <div class="word-chips" id="newWords"></div>
          </article>
          <article class="side-word-card">
            <div><h3>풀린말</h3><p>주소 연결에서 풀어낸 이름</p></div>
            <div class="word-chips" id="releasedWords"></div>
          </article>
        </aside>
      </div>
    </section>

    <section class="legacy" aria-labelledby="legacyTitle">
      <div>
        <p class="kicker">열두 해의 생각</p>
        <h2 id="legacyTitle">그때의 뜻을<br><span>오늘의 쓰임으로.</span></h2>
      </div>
      <div class="legacy-copy">
        <article><strong>설치 없이</strong><p>사용을 강요하지 않고, 주소 하나만으로 어디서든 엽니다.</p></article>
        <article><strong>주소 대신 이름</strong><p>긴 URL 대신 기억하기 쉬운 상호와 서비스 이름을 씁니다.</p></article>
        <article><strong>한 화면, 한 목적</strong><p>설명은 줄이고 이동과 검색에 필요한 정보만 보여줍니다.</p></article>
        <article><strong>작게 시작해 확장</strong><p>개인 시작 페이지에서 검증한 뒤 이음말 서비스로 키웁니다.</p></article>
      </div>
    </section>
  </main>

  <footer><span>#인투샾</span><span>이름으로 여는 나의 첫 화면</span></footer>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>
  <div class="personal-undo" id="personalUndo" role="status" aria-live="polite" hidden>
    <span id="personalUndoText"></span>
    <button id="personalUndoButton" type="button">되돌리기</button>
  </div>
  <dialog class="login-dialog personal-dialog" id="personalLinkDialog" aria-labelledby="personalLinkTitle">
    <form class="login-form" id="personalLinkForm" method="dialog">
      <h2 id="personalLinkTitle">내 이음말 추가</h2>
      <p>이 브라우저에만 저장됩니다. 주소는 http 또는 https만 넣을 수 있습니다.</p>
      <label>주소<input id="personalLinkUrl" type="text" inputmode="url" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="https://" maxlength="2000" required></label>
      <label>이름<input id="personalLinkName" type="text" autocomplete="off" maxlength="100" placeholder="비우면 주소로 채웁니다"></label>
      <label>메모 한 줄<input id="personalLinkNote" type="text" autocomplete="off" maxlength="200" placeholder="예: 매주 월요일 정산 확인"></label>
      <label>다음 할 일<input id="personalLinkTodo" type="text" autocomplete="off" maxlength="200" placeholder="예: 8월 세금계산서 발행 (완료 체크 가능)"></label>
      <label>판<select id="personalLinkBoard"></select></label>
      <p class="login-error" id="personalLinkError" role="alert"></p>
      <div class="login-actions">
        <button id="personalLinkCancel" type="button">취소</button>
        <button type="submit">저장</button>
      </div>
    </form>
  </dialog>
  <dialog class="login-dialog personal-dialog" id="personalBackupDialog" aria-labelledby="personalBackupTitle">
    <div class="login-form">
      <h2 id="personalBackupTitle">백업·복원</h2>
      <div class="personal-dialog-body" id="personalBackupBody"></div>
      <div class="login-actions"><button id="personalBackupClose" type="button">닫기</button></div>
    </div>
  </dialog>
  <dialog class="login-dialog personal-dialog" id="personalDedupeDialog" aria-labelledby="personalDedupeTitle">
    <div class="login-form">
      <h2 id="personalDedupeTitle">중복 정리</h2>
      <div class="personal-dialog-body" id="personalDedupeBody"></div>
      <div class="login-actions"><button id="personalDedupeClose" type="button">닫기</button></div>
    </div>
  </dialog>
  <dialog class="login-dialog" id="adminLoginDialog" aria-labelledby="adminLoginTitle">
    <form class="login-form" id="adminLoginForm" method="dialog">
      <h2 id="adminLoginTitle">관리자 로그인</h2>
      <p>관리자 계정으로 이음말을 관리합니다.</p>
      <label>아이디<input id="adminUsername" name="username" type="text" autocomplete="username" maxlength="64" required></label>
      <label>비밀번호<input id="adminPassword" name="password" type="password" autocomplete="current-password" maxlength="256" required></label>
      <p class="login-error" id="adminLoginError" role="alert"></p>
      <div class="login-actions">
        <button id="adminLoginCancel" type="button">취소</button>
        <button type="submit">로그인</button>
      </div>
    </form>
  </dialog>

  <script src="legacy-catalog.js?v=20260816-3"></script>
  <script>
    (() => {
      'use strict';

      const services = {
        '네이버': ['https://www.naver.com/', 'https://search.naver.com/search.naver?query='],
        '구글': ['https://www.google.com/', 'https://www.google.com/search?q='],
        '유튜브': ['https://www.youtube.com/?gl=KR', 'https://www.youtube.com/results?search_query='],
        '다음': ['https://www.daum.net/', 'https://search.daum.net/search?q='],
        'Bing': ['https://www.bing.com/', 'https://www.bing.com/search?q='],
        '빙': ['https://www.bing.com/', 'https://www.bing.com/search?q='],
        '구글이미지': ['https://images.google.com/', 'https://www.google.com/search?tbm=isch&q='],
        '쿠팡': ['https://www.coupang.com/', 'https://www.coupang.com/np/search?q='],
        '네이버지도': ['https://map.naver.com/', 'https://map.naver.com/p/search/'],
        '지도': ['https://map.naver.com/', 'https://map.naver.com/p/search/'],
        '쇼핑': ['https://shopping.naver.com/', 'https://search.shopping.naver.com/search/all?query='],
        '네이버쇼핑': ['https://shopping.naver.com/', 'https://search.shopping.naver.com/search/all?query='],
        'ChatGPT': ['https://chatgpt.com/', 'https://www.google.com/search?q=site%3Achatgpt.com+'],
        '챗지피티': ['https://chatgpt.com/', 'https://www.google.com/search?q=site%3Achatgpt.com+'],
        'Gemini': ['https://gemini.google.com/', 'https://www.google.com/search?q='],
        '제미나이': ['https://gemini.google.com/', 'https://www.google.com/search?q='],
        '한통': ['https://smartstore.naver.com/hantongbox', 'https://search.naver.com/search.naver?query='],
        '한통도시락': ['https://smartstore.naver.com/hantongbox', 'https://search.naver.com/search.naver?query='],
        '인투샾': ['https://intosharp.com/', 'https://www.google.com/search?q=site%3Aintosharp.com+'],
        '나스파인더': ['https://nasfinder.com/', 'https://www.google.com/search?q=site%3Anasfinder.com+'],
        'GitHub': ['https://github.com/armsone', 'https://github.com/search?q='],
        '깃허브': ['https://github.com/armsone', 'https://github.com/search?q=']
      };

      const form = document.getElementById('commandForm');
      const input = document.getElementById('commandInput');
      const toast = document.getElementById('toast');
      const themeToggle = document.getElementById('themeToggle');
      const legacyCatalog = window.INTO_LEGACY_CATALOG || [];
      const legacyAliases = window.INTO_LEGACY_ALIASES || {};
      const currentSearchService = document.getElementById('currentSearchService');
      const searchServiceButtons = document.querySelectorAll('[data-search-service]');
      let selectedService = localStorage.getItem('intosh-search-service') || '네이버';
      if (!services[selectedService]) selectedService = '네이버';

      function showToast(message) {
        toast.textContent = message;
        toast.classList.add('show');
        window.clearTimeout(showToast.timer);
        showToast.timer = window.setTimeout(() => toast.classList.remove('show'), 1800);
      }

      function openInNewTab(url) {
        window.open(url, '_blank', 'noopener,noreferrer');
      }

      function navigate(rawValue) {
        const value = rawValue.trim().replace(/^#+\s*/, '');
        if (!value) {
          showToast('이동할 이름이나 검색어를 입력하세요.');
          input.focus();
          return;
        }

        if (/^(https?:\/\/|www\.)/i.test(value)) {
          openInNewTab(/^https?:\/\//i.test(value) ? value : `https://${value}`);
          return;
        }

        const firstSpace = value.search(/\s/);
        const name = firstSpace === -1 ? value : value.slice(0, firstSpace);
        const query = firstSpace === -1 ? '' : value.slice(firstSpace).trim();
        const serviceKey = Object.keys(services).find(key => key.toLocaleLowerCase('ko-KR') === name.toLocaleLowerCase('ko-KR'));

        // 입력 전체가 내 이음말 이름과 정확히 같으면 바로 연다. 공백이 든 이름("우리 은행")도 포함.
        // 단, "네이버 날씨"처럼 첫 단어가 포털 이름이고 검색어가 이어지면 포털 검색을 우선한다.
        if (!query || !serviceKey) {
          const personalUrl = window.intoSharpPersonal?.resolveAlias(value);
          if (personalUrl) {
            openInNewTab(personalUrl);
            return;
          }
        }

        if (serviceKey) {
          const [home, search] = services[serviceKey];
          openInNewTab(query ? search + encodeURIComponent(query) : home);
          return;
        }

        const managedAlias = window.intoSharpAdmin?.resolveAlias(name);
        if (managedAlias?.url) {
          openInNewTab(managedAlias.url);
          return;
        }
        if (managedAlias?.managed) {
          openInNewTab(services[selectedService][1] + encodeURIComponent(value));
          return;
        }

        const aliasKey = Object.keys(legacyAliases).find(key => key.toLocaleLowerCase('ko-KR') === name.toLocaleLowerCase('ko-KR'));
        if (aliasKey) {
          openInNewTab(legacyAliases[aliasKey]);
          return;
        }

        openInNewTab(services[selectedService][1] + encodeURIComponent(value));
      }

      function appendWordChips(groupTitle, containerId) {
        const group = legacyCatalog.find(item => item.title === groupTitle);
        const container = document.getElementById(containerId);
        if (!group || !container) return;

        group.items.forEach(([name, url]) => {
          const chip = document.createElement(url ? 'a' : 'span');
          chip.className = 'word-chip';
          chip.textContent = name;
          if (url) chip.href = url;
          container.appendChild(chip);
        });
      }

      function applyFavicon() {}

      function applyFavicons(root = document) {
        const links = root.matches?.('a') && root.querySelector('.site-mark') ? [root] : root.querySelectorAll('.link-list a');
        links.forEach(link => applyFavicon(link.querySelector('.site-mark'), link.href));
      }

      function applySearchFavicons() {
        searchServiceButtons.forEach(button => {
          const service = services[button.dataset.searchService];
          if (service) applyFavicon(button.querySelector('.quick-mark'), service[0]);
        });
      }

      window.intoSharpApplyFavicons = applyFavicons;

      const signatureDescriptions = {
        '스마트스토어센터': '주문 쌓여도 난 스마트',
        '네이버 톡톡': '고객이 톡, 답장은 척',
        '페이앱': '결제하고 마음은 편히',
        '유니포스트': '증빙의 디지털 우체국',
        '아이파킹': '주차 고민도 P에 주차',
        '장집카페': '집 얘기의 장기 체류지',
        '한통 스토어': '한 통이면 한 끼 해결',
        '한통 블로그': '도시락 밖 맛있는 얘기',
        'Instagram': '맛은 입으로, 멋은 피드',
        '네이버TV': '짧게 재생, 길게 기억',
        '클리앙': '기기는 새것, 얘긴 무한',
        '다모앙': '다 모앙? 다 모였어요',
        '뽐뿌': '지갑 닫고 정보 열기',
        '루리웹': '취향도 레벨도 만렙',
        'KMUG': '사과 한입, 얘기 한가득',
        'Reddit': '시간 두고 가는 토끼굴',
        'YouTube': '한 편만의 단골 거짓말',
        '유튜브': '한 편만의 단골 거짓말',
        'Netflix': '다음 화와 수면 협상',
        'TVING': '놓친 본방 다시 잡기',
        'Wavve': '콘텐츠 파도타기',
        '쿠팡플레이': '로켓은 영상도 쏜다',
        'JustWatch': '영화 찾다 끝날 일 끝',
        '딴지일보': '세상에 한 번 딴지',
        'ChatGPT': '질문하면 생각이 줄줄',
        'Gemini': '막힌 두뇌에 별 하나',
        'GitHub': '버그는 숨고 커밋은 남는다',
        'App Store Connect': '심사는 애플, 설렘은 나',
        'iCloud': '잃은 파일은 여기',
        'Google': '몰라도 구글, 알아도 구글',
        'dvdprime': '화질 한 톨도 진지하게',
        '비율계산': '감 대신 숫자로 딱',
        '그라데이션': '두 색의 평화 협정',
        '생활코딩': '코딩을 생활처럼',
        '픽슬러': '사진 변신 한 클릭',
        '위디스크': '파일은 위로, 고민은 아래',
        '구글번역': '말은 달라도 뜻은 통하게',
        '인투샾': '주소 말고 이름만',
        '나스파인더': '숨은 NAS 이름부터',
        'armsone GitHub': '커밋 쌓고 밤은 줄고',
        '네이버블로그': '오늘 기록, 내일 검색',
        '에어처치': '예배는 가까이, 은혜는 멀리',
        '착한나눔': '마음 나누고 온기는 곱',
        '맥설정': '맥은 그대로, 손맛은 내게',
        '노마드코더맥설정': '코드는 노마드, 설정은 정착',
        '라즈베리': '작은 보드, 큰 궁리',
        '쿠팡': '장바구니보다 빠른 로켓',
        '비즈프린트': '화면을 손에 잡히게',
        '명함천사': '첫인상을 한 장에 착',
        '자석전단': '붙으면 기억도 착',
        '명성디자인': '이름값 하는 디자인',
        'GMP': '선명하게, 말끔하게',
        '제이스토리': '물건에도 이야기를',
        '밴드': '모임 묶고 소식 챙기고',
        '네이버지도': '길치도 아는 척',
        '다음지도': '다음 길은 다음이 안다',
        '구글포토': '흩어진 추억 모으기',
        'Flickr': '사진이 오래 말하는 곳',
        'RSS리더': '소식 모으고 광고 덜고',
        '지메일': '쌓인 메일도 검색 한 번',
        '애플': '사과 한입, 지갑은 가볍게',
        '애플코리아': '사과 소식 아삭하게',
        '백투더맥': '돌아와도 결국 맥',
        '재즈라디오': '공간에 재즈 한 스푼',
        '광성교회': '마음에 평안 한 칸',
        '광주교회': '따뜻한 말씀 한 줄',
        '한소망': '소망 하나, 탭 하나',
        '만나': '오늘의 말씀을 만나',
        '오륜': '마음의 바퀴 반듯하게',
        '호산나': '한 주의 숨 고르기',
        '갓피플': '믿음 사이 클릭 하나',
        '생명의삶': '오늘을 살게 하는 말씀'
      };

      const groupVoices = {
        '일': ['클릭 한 번, 퇴근 한 칸', '미룬 일과 눈 맞추기', '일은 줄고 완료는 늘고'],
        '한통': ['한 통이면 한 끼 해결', '든든함을 한 통 가득', '맛있는 일은 한통부터'],
        '이야기마당': ['수다 반, 정보 반', '읽다가 시간 순삭', '댓글까지 보고 갑니다'],
        '볼거리': ['재생하면 시간 순삭', '취침 시간과 협상 중', '딱 하나만 보기 실패'],
        '연장': ['사람은 역시 도구빨', '막힌 일에 디지털 기름', '클릭하면 손이 두 개 더'],
        '패밀리 사이트': ['따로 있어도 한집', '우리 식구 인터넷 문패', '가까운 링크 한 묶음'],
        '살거리': ['구경만 한다는 출발점', '필요와 욕심의 경계', '장바구니가 부르는 곳'],
        '들머리': ['웹 여행 첫 정거장', '인터넷 대문 활짝', '길 많아도 시작은 여기'],
        '쉼': ['쉬러 와서 한 시간', '손가락의 합법적 휴가', '아무것도 안 해도 클릭'],
        '믿음': ['마음도 새로고침', '평안을 로그인', '하루에 쉼표 하나']
      };

      function playfulDescription(name, groupTitle, fallback = '') {
        if (signatureDescriptions[name]) return signatureDescriptions[name];
        const voices = groupVoices[groupTitle];
        if (!voices?.length) return fallback || '한 번에 바로 가기';
        const seed = Array.from(name).reduce((total, character) => total + character.codePointAt(0), 0);
        return voices[seed % voices.length];
      }

      function refreshDescriptions() {
        document.querySelectorAll('.card').forEach(card => {
          const groupTitle = card.querySelector('.card-title h3')?.textContent.trim() || '';
          card.querySelectorAll('.link-list a').forEach(link => {
            const name = link.querySelector('strong')?.textContent.trim();
            const description = link.querySelector('small');
            if (name && description) description.textContent = playfulDescription(name, groupTitle, description.textContent);
          });
        });
      }

      function makeDestinationsOpenInNewTabs() {
        document.querySelectorAll('main a[href]').forEach(link => {
          link.target = '_blank';
          link.rel = 'noopener noreferrer';
        });
      }

      window.intoSharpDescription = playfulDescription;

      function organizeLegacyCatalog() {
        const destinations = {
          '업무': 'workLinks',
          '쇼핑': 'shoppingLinks',
          '포털': 'portalLinks',
          '즐겨찾기': 'leisureLinks',
          '옛 미디어': 'mediaLinks',
          'IT': 'toolLinks',
          '종교': 'faithLinks',
          '패밀리': 'familyLinks'
        };
        const itemDestinations = {
          '위디스크': 'toolLinks',
          '제이스토리': 'shoppingLinks',
          '구글번역': 'toolLinks',
          '딴지일보': 'mediaLinks',
          '네이버블로그': 'familyLinks'
        };
        const featuredGroups = new Set(['마중말', '새 이음말', '풀린말']);
        const seenNames = new Set(Object.keys(services).map(name => name.toLocaleLowerCase('ko-KR')));
        const seenUrls = new Set();

        function urlKey(url) {
          try {
            const parsed = new URL(url, window.location.href);
            return `${parsed.hostname.replace(/^www\./, '').toLowerCase()}${parsed.pathname.replace(/\/$/, '')}${parsed.search}`;
          } catch (_) {
            return url;
          }
        }

        document.querySelectorAll('a[href]').forEach(link => {
          const name = link.querySelector('strong')?.textContent || link.textContent;
          seenNames.add(name.trim().toLocaleLowerCase('ko-KR'));
          seenUrls.add(urlKey(link.href));
        });

        function isNew(name, url) {
          const nameKey = name.trim().toLocaleLowerCase('ko-KR');
          const addressKey = urlKey(url);
          if (!url || seenNames.has(nameKey) || seenUrls.has(addressKey)) return false;
          seenNames.add(nameKey);
          seenUrls.add(addressKey);
          return true;
        }

        function makeLink(name, url, source) {
          const link = document.createElement('a');
          link.href = url;
          const mark = document.createElement('span');
          mark.className = 'site-mark';
          mark.textContent = Array.from(name.replace(/[^0-9A-Za-z가-힣]/g, '')).slice(0, 2).join('').toUpperCase() || '#';
          const copy = document.createElement('span');
          copy.className = 'link-copy';
          const strong = document.createElement('strong');
          strong.textContent = name;
          const small = document.createElement('small');
          small.textContent = `${source}에서 이어옴`;
          copy.append(strong, small);
          link.append(mark, copy);
          return link;
        }

        legacyCatalog.forEach(group => {
          const targetId = destinations[group.title];
          if (!targetId) return;
          const target = document.getElementById(targetId);
          group.items.forEach(([name, url]) => {
            if (isNew(name, url)) target.appendChild(makeLink(name, url, group.title));
          });
        });

        const remaining = [];
        legacyCatalog.forEach(group => {
          if (featuredGroups.has(group.title) || destinations[group.title]) return;
          group.items.forEach(([name, url]) => {
            const targetId = itemDestinations[name];
            if (targetId) {
              const target = document.getElementById(targetId);
              if (isNew(name, url)) target.appendChild(makeLink(name, url, '확인한 현재 주소'));
              return;
            }
            if (isNew(name, url)) remaining.push([name, url]);
          });
        });

        new Set([...Object.values(destinations), ...Object.values(itemDestinations)]).forEach(targetId => {
          const target = document.getElementById(targetId);
          target.closest('.card').querySelector('.card-head small').textContent = `${target.querySelectorAll('a').length}곳`;
        });

        if (!remaining.length) {
          document.querySelector('.archive').hidden = true;
          return;
        }

        const grid = document.getElementById('archiveGrid');
        const card = document.createElement('article');
        card.className = 'archive-card wide';
        const header = document.createElement('header');
        const title = document.createElement('h3');
        title.textContent = `그 밖의 이음말 · ${remaining.length}`;
        const note = document.createElement('p');
        note.textContent = '위 분류와 겹치지 않고, 따로 분류하기에는 수가 적은 이음말';
        header.append(title, note);
        const chips = document.createElement('div');
        chips.className = 'archive-chips';
        remaining.forEach(([name, url]) => {
          const chip = document.createElement('a');
          chip.className = 'archive-chip';
          chip.textContent = name;
          chip.href = url;
          chips.appendChild(chip);
        });
        card.append(header, chips);
        grid.appendChild(card);
        document.getElementById('archiveCount').textContent = `${remaining.length}개 이음말`;
      }
      appendWordChips('마중말', 'welcomeWords');
      appendWordChips('새 이음말', 'newWords');
      appendWordChips('풀린말', 'releasedWords');
      organizeLegacyCatalog();
      refreshDescriptions();
      makeDestinationsOpenInNewTabs();
      applyFavicons();
      applySearchFavicons();

      form.addEventListener('submit', async event => {
        event.preventDefault();
        navigate(input.value);
      });

      document.querySelectorAll('[data-example]').forEach(button => {
        button.addEventListener('click', () => {
          input.value = button.dataset.example;
          input.focus();
        });
      });

      function selectSearchService(name, focusInput) {
        selectedService = services[name] ? name : '네이버';
        localStorage.setItem('intosh-search-service', selectedService);
        currentSearchService.textContent = selectedService;
        input.placeholder = `${selectedService}에서 검색어 입력`;
        searchServiceButtons.forEach(button => {
          const active = button.dataset.searchService === selectedService;
          button.classList.toggle('active', active);
          button.setAttribute('aria-pressed', String(active));
        });
        if (focusInput) input.focus();
      }

      searchServiceButtons.forEach(button => {
        button.addEventListener('click', () => selectSearchService(button.dataset.searchService, true));
      });
      selectSearchService(selectedService, false);

      document.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', () => {
          document.querySelectorAll('[data-filter]').forEach(item => {
            const active = item === button;
            item.classList.toggle('active', active);
            item.setAttribute('aria-pressed', String(active));
          });
          document.querySelectorAll('[data-group]').forEach(card => {
            card.hidden = button.dataset.filter !== 'all' && card.dataset.group !== button.dataset.filter;
          });
        });
      });

      function applyTheme(theme) {
        document.documentElement.dataset.theme = theme;
        themeToggle.textContent = theme === 'dark' ? '☀' : '◐';
        themeToggle.setAttribute('aria-label', theme === 'dark' ? '밝은 화면으로 전환' : '어두운 화면으로 전환');
        document.querySelector('meta[name="theme-color"]').content = theme === 'dark' ? '#111613' : '#f4f0e7';
      }

      const savedTheme = localStorage.getItem('intosh-theme');
      applyTheme(savedTheme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
      themeToggle.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('intosh-theme', next);
        applyTheme(next);
      });

      function updateClock() {
        document.getElementById('clock').textContent = new Intl.DateTimeFormat('ko-KR', {
          hour: '2-digit', minute: '2-digit', hour12: true
        }).format(new Date());
      }
      updateClock();
      window.setInterval(updateClock, 30000);

      document.addEventListener('keydown', event => {
        if (event.key === '/' && document.activeElement !== input) {
          event.preventDefault();
          input.focus();
        }
      });
    })();
  </script>
  <script src="admin.js?v=20260826-1"></script>
  <script src="personal.js?v=20260913-1"></script>
  <script src="modern.js?v=20260816-1"></script>
</body>
</html>
