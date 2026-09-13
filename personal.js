(() => {
  'use strict';

  // 내 이음말: 이 브라우저(localStorage)에만 저장되는 개인 링크판.
  // 서버로 보내지 않으며, 사이트 데이터를 지우면 함께 사라진다. 백업 파일이 유일한 보관 수단이다.

  const STORAGE_KEY = 'intosh-personal-v1';
  const NAME_LIMIT = 100;
  const NOTE_LIMIT = 200;
  const URL_LIMIT = 2000;
  const UNDO_MS = 8000;
  const DEFAULT_BOARDS = ['매일', '가끔', '업무'];
  const SORT_LABELS = { manual: '내 순서', name: '이름순', added: '추가순', opened: '최근 연 순' };

  const root = document.getElementById('personal');
  if (!root || !('localStorage' in window)) return;

  const el = {
    tabs: root.querySelector('#personalTabs'),
    list: root.querySelector('#personalList'),
    empty: root.querySelector('#personalEmpty'),
    search: root.querySelector('#personalSearch'),
    sort: root.querySelector('#personalSort'),
    count: root.querySelector('#personalCount'),
    addLink: root.querySelector('#personalAddLink'),
    addBoard: root.querySelector('#personalAddBoard'),
    renameBoard: root.querySelector('#personalRenameBoard'),
    deleteBoard: root.querySelector('#personalDeleteBoard'),
    backup: root.querySelector('#personalBackup'),
    dedupe: root.querySelector('#personalDedupe'),
    linkDialog: document.getElementById('personalLinkDialog'),
    linkForm: document.getElementById('personalLinkForm'),
    linkTitle: document.getElementById('personalLinkTitle'),
    linkName: document.getElementById('personalLinkName'),
    linkUrl: document.getElementById('personalLinkUrl'),
    linkNote: document.getElementById('personalLinkNote'),
    linkTodo: document.getElementById('personalLinkTodo'),
    linkBoard: document.getElementById('personalLinkBoard'),
    linkError: document.getElementById('personalLinkError'),
    linkCancel: document.getElementById('personalLinkCancel'),
    backupDialog: document.getElementById('personalBackupDialog'),
    backupBody: document.getElementById('personalBackupBody'),
    backupClose: document.getElementById('personalBackupClose'),
    dedupeDialog: document.getElementById('personalDedupeDialog'),
    dedupeBody: document.getElementById('personalDedupeBody'),
    dedupeClose: document.getElementById('personalDedupeClose'),
    undoBar: document.getElementById('personalUndo'),
    undoText: document.getElementById('personalUndoText'),
    undoButton: document.getElementById('personalUndoButton'),
    toast: document.getElementById('toast'),
  };

  let state = null;
  let undoSnapshot = null;
  let undoTimer = 0;
  let editingLinkId = null;
  // 마지막으로 읽거나 쓴 localStorage 원문. 저장 직전에 다시 읽어 다른 탭이 먼저 바꿨는지 확인한다.
  let lastSeen = null;
  // 저장 원본을 온전히 읽지 못했을 때 { raw, reason }. 잠긴 동안은 어떤 쓰기도 하지 않아 원본을 덮어쓰지 않는다.
  let writeLock = null;

  // ---------- 공통 도우미 ----------

  function showToast(message) {
    el.toast.textContent = message;
    el.toast.classList.add('show');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => el.toast.classList.remove('show'), 2600);
  }

  function makeId(prefix) {
    if (globalThis.crypto?.randomUUID) return `${prefix}-${crypto.randomUUID()}`;
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
  }

  function now() {
    return Math.floor(Date.now() / 1000);
  }

  function text(value, limit) {
    return String(value ?? '').replace(/\s+/g, ' ').trim().slice(0, limit);
  }

  function normalizeUrl(raw) {
    const value = String(raw ?? '').trim();
    if (!value || value.length > URL_LIMIT) return '';
    const withScheme = /^https?:\/\//i.test(value) ? value : `https://${value}`;
    try {
      const url = new URL(withScheme);
      if (!['http:', 'https:'].includes(url.protocol) || !url.hostname.includes('.')) return '';
      return url.href;
    } catch (_) {
      return '';
    }
  }

  // 중복 판정 키. 정규화된 주소 전체를 그대로 쓴다.
  // 프로토콜·www·포트·경로 끝 슬래시·쿼리·해시가 하나라도 다르면 다른 링크로 보아, 서로 다른 주소를 중복으로 지우지 않는다.
  function urlKey(url) {
    return normalizeUrl(url) || String(url ?? '');
  }

  // 화면 표시용 짧은 주소. 비교에는 쓰지 않는다.
  function displayUrl(url) {
    try {
      const parsed = new URL(url);
      return `${parsed.host}${parsed.pathname === '/' ? '' : parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch (_) {
      return String(url ?? '');
    }
  }

  function hasSort(value) {
    return typeof value === 'string' && Object.prototype.hasOwnProperty.call(SORT_LABELS, value);
  }

  function makeMark(name) {
    return Array.from(name.replace(/[^0-9A-Za-z가-힣]/g, '')).slice(0, 2).join('').toUpperCase() || '#';
  }

  function button(label, className, title = label) {
    const node = document.createElement('button');
    node.type = 'button';
    node.className = className;
    node.textContent = label;
    node.title = title;
    node.setAttribute('aria-label', title);
    return node;
  }

  // ---------- 상태 ----------

  function cleanLink(raw) {
    if (!raw || typeof raw !== 'object') return null;
    const url = normalizeUrl(raw.url);
    if (!url) return null;
    const name = text(raw.name, NAME_LIMIT) || displayUrl(url).slice(0, NAME_LIMIT);
    return {
      id: typeof raw.id === 'string' && raw.id ? raw.id.slice(0, 80) : makeId('link'),
      name,
      url,
      note: text(raw.note, NOTE_LIMIT),
      todo: text(raw.todo, NOTE_LIMIT),
      done: Boolean(raw.done),
      addedAt: Number.isFinite(Number(raw.addedAt)) && Number(raw.addedAt) > 0 ? Number(raw.addedAt) : now(),
      openedAt: Number.isFinite(Number(raw.openedAt)) ? Number(raw.openedAt) : 0,
      openCount: Number.isFinite(Number(raw.openCount)) ? Number(raw.openCount) : 0,
    };
  }

  function cleanBoard(raw, index) {
    if (!raw || typeof raw !== 'object') return null;
    const links = Array.isArray(raw.links) ? raw.links.map(cleanLink).filter(Boolean) : [];
    return {
      id: typeof raw.id === 'string' && raw.id ? raw.id.slice(0, 80) : makeId('board'),
      title: text(raw.title, 40) || `판 ${index + 1}`,
      sort: hasSort(raw.sort) ? raw.sort : 'manual',
      links,
    };
  }

  function cleanState(raw) {
    const boards = Array.isArray(raw?.boards) ? raw.boards.map(cleanBoard).filter(Boolean) : [];
    if (!boards.length) DEFAULT_BOARDS.forEach(title => boards.push({ id: makeId('board'), title, sort: 'manual', links: [] }));
    // 같은 ID가 둘 이상이면 뒤의 것에 새 ID를 준다. 수정·삭제가 엉뚱한 항목이나 여러 판에 동시에 걸리지 않게 한다.
    const seenBoards = new Set();
    const seenLinks = new Set();
    boards.forEach(board => {
      if (seenBoards.has(board.id)) board.id = makeId('board');
      seenBoards.add(board.id);
      board.links.forEach(link => {
        if (seenLinks.has(link.id)) link.id = makeId('link');
        seenLinks.add(link.id);
      });
    });
    const activeBoard = boards.some(board => board.id === raw?.activeBoard) ? raw.activeBoard : boards[0].id;
    return { version: 1, boards, activeBoard, updatedAt: Number(raw?.updatedAt) || 0 };
  }

  function countRawLinks(boards) {
    return boards.reduce((sum, board) => sum + (Array.isArray(board?.links) ? board.links.length : 0), 0);
  }

  function lockWrites(raw, reason) {
    writeLock = { raw: String(raw ?? ''), reason };
    showToast(`${reason} 원본을 덮어쓰지 않도록 저장을 멈췄습니다. 백업·복원에서 원본을 내보내거나 새로 시작하세요.`);
  }

  function unlockWrites() {
    writeLock = null;
    try {
      lastSeen = localStorage.getItem(STORAGE_KEY);
    } catch (_) {
      lastSeen = null;
    }
  }

  function load() {
    let stored = null;
    try {
      stored = localStorage.getItem(STORAGE_KEY);
    } catch (_) {
      state = cleanState(null);
      lockWrites('', '이 브라우저의 저장소를 읽지 못했습니다.');
      return;
    }
    lastSeen = stored;
    writeLock = null;
    if (!stored) {
      state = cleanState(null);
      return;
    }
    let parsed = null;
    try {
      parsed = JSON.parse(stored);
    } catch (_) {
      parsed = null;
    }
    if (!parsed || typeof parsed !== 'object' || !Array.isArray(parsed.boards)) {
      // 읽기 자체가 실패했다. 빈 판을 메모리에만 두고, 원본은 사용자가 처리하기 전까지 그대로 남긴다.
      state = cleanState(null);
      lockWrites(stored, '저장된 내 이음말을 읽지 못했습니다.');
      return;
    }
    state = cleanState(parsed);
    const dropped = countRawLinks(parsed.boards) - allLinks().length;
    if (dropped > 0) lockWrites(stored, `저장된 링크 ${dropped}곳을 읽지 못했습니다.`);
  }

  // 반환값: true 저장됨 / false 저장 안 됨(메모리 상태는 호출자가 되돌림) / 'conflict' 다른 탭 변경을 먼저 불러옴(메모리 상태 교체됨)
  function persist() {
    if (writeLock) {
      showToast(`${writeLock.reason} 저장을 멈춘 상태입니다. 백업·복원에서 원본을 내보내거나 새로 시작하세요.`);
      return false;
    }
    let current = null;
    try {
      current = localStorage.getItem(STORAGE_KEY);
    } catch (_) {
      current = lastSeen;
    }
    if (current !== lastSeen) {
      load();
      renderAll();
      showToast('다른 탭에서 바뀐 내용을 먼저 불러왔습니다. 방금 한 작업을 다시 해 주세요.');
      return 'conflict';
    }
    state.updatedAt = now();
    try {
      const serialized = JSON.stringify(state);
      localStorage.setItem(STORAGE_KEY, serialized);
      lastSeen = serialized;
      return true;
    } catch (error) {
      const quota = error?.name === 'QuotaExceededError' || error?.code === 22 || error?.code === 1014;
      showToast(quota
        ? '브라우저 저장 공간이 가득 찼습니다. 백업 파일로 내보낸 뒤 링크를 줄이세요.'
        : '이 브라우저에 저장하지 못했습니다. 시크릿 모드나 저장 차단 설정을 확인하세요.');
      return false;
    }
  }

  // keepUndo: 판 전환처럼 데이터를 바꾸지 않는 변경만 true. 그 밖의 변경은 이전 되돌리기를 무효화해,
  // 삭제 뒤에 추가·수정한 내용이 되돌리기로 덮어써지지 않게 한다.
  function commit(change, undoLabel = '', keepUndo = false) {
    const before = JSON.stringify(state);
    change();
    const result = persist();
    if (result !== true) {
      if (result === false) state = cleanState(JSON.parse(before));
      renderAll();
      return false;
    }
    if (undoLabel) offerUndo(before, undoLabel);
    else if (!keepUndo) hideUndo();
    renderAll();
    return true;
  }

  function activeBoard() {
    return state.boards.find(board => board.id === state.activeBoard) || state.boards[0];
  }

  function allLinks() {
    return state.boards.flatMap(board => board.links.map(link => ({ board, link })));
  }

  // ---------- 되돌리기 ----------

  function offerUndo(snapshot, label) {
    undoSnapshot = snapshot;
    el.undoText.textContent = label;
    el.undoBar.hidden = false;
    window.clearTimeout(undoTimer);
    undoTimer = window.setTimeout(hideUndo, UNDO_MS);
  }

  function hideUndo() {
    undoSnapshot = null;
    el.undoBar.hidden = true;
    window.clearTimeout(undoTimer);
  }

  el.undoButton.addEventListener('click', () => {
    if (!undoSnapshot) return;
    const restored = cleanState(JSON.parse(undoSnapshot));
    const previous = state;
    hideUndo();
    state = restored;
    const result = persist();
    if (result === true) showToast('되돌렸습니다.');
    else if (result === false) state = previous;
    renderAll();
  });

  // ---------- 렌더링 ----------

  function renderTabs() {
    el.tabs.replaceChildren();
    state.boards.forEach(board => {
      const tab = document.createElement('button');
      tab.type = 'button';
      tab.setAttribute('role', 'tab');
      tab.dataset.boardId = board.id;
      tab.textContent = `${board.title} ${board.links.length}`;
      const active = board.id === state.activeBoard;
      tab.classList.toggle('active', active);
      tab.setAttribute('aria-selected', String(active));
      tab.tabIndex = active ? 0 : -1;
      el.tabs.appendChild(tab);
    });
  }

  function sortLinks(items, mode) {
    const list = [...items];
    if (mode === 'name') list.sort((a, b) => a.link.name.localeCompare(b.link.name, 'ko-KR'));
    else if (mode === 'added') list.sort((a, b) => b.link.addedAt - a.link.addedAt);
    else if (mode === 'opened') list.sort((a, b) => (b.link.openedAt - a.link.openedAt) || (b.link.openCount - a.link.openCount));
    return list;
  }

  function currentItems() {
    const query = text(el.search.value, 100).toLocaleLowerCase('ko-KR');
    const board = activeBoard();
    const scope = query ? allLinks() : board.links.map(link => ({ board, link }));
    const filtered = query
      ? scope.filter(({ link }) => [link.name, link.url, link.note, link.todo].some(field => field.toLocaleLowerCase('ko-KR').includes(query)))
      : scope;
    return { query, board, items: sortLinks(filtered, board.sort) };
  }

  function makeRow({ board, link }, index, total, showBoard, manual) {
    const row = document.createElement('div');
    row.className = 'personal-row';
    row.dataset.linkId = link.id;
    row.dataset.boardId = board.id;

    const anchor = document.createElement('a');
    anchor.href = link.url;
    anchor.target = '_blank';
    anchor.rel = 'noopener noreferrer';
    anchor.dataset.personalOpen = link.id;
    const mark = document.createElement('span');
    mark.className = 'site-mark';
    mark.textContent = makeMark(link.name);
    const copy = document.createElement('span');
    copy.className = 'link-copy';
    const strong = document.createElement('strong');
    strong.textContent = link.name;
    const small = document.createElement('small');
    small.textContent = link.note || displayUrl(link.url);
    if (showBoard) small.textContent = `${board.title} · ${small.textContent}`;
    copy.append(strong, small);
    anchor.append(mark, copy);
    row.appendChild(anchor);

    if (link.todo) {
      const todo = document.createElement('label');
      todo.className = `personal-todo${link.done ? ' done' : ''}`;
      const check = document.createElement('input');
      check.type = 'checkbox';
      check.checked = link.done;
      check.dataset.personalDone = link.id;
      check.setAttribute('aria-label', `${link.name} 할 일 완료 표시`);
      const label = document.createElement('span');
      label.textContent = link.todo;
      todo.append(check, label);
      row.appendChild(todo);
    }

    const controls = document.createElement('div');
    controls.className = 'personal-controls';
    if (manual) {
      const up = button('↑', 'admin-control', `${link.name} 위로 이동`);
      const down = button('↓', 'admin-control', `${link.name} 아래로 이동`);
      up.dataset.personalAction = 'move-up';
      down.dataset.personalAction = 'move-down';
      up.disabled = index === 0;
      down.disabled = index === total - 1;
      controls.append(up, down);
    }
    const edit = button('✎', 'admin-control', `${link.name} 수정`);
    edit.dataset.personalAction = 'edit';
    const remove = button('×', 'admin-control admin-danger', `${link.name} 삭제`);
    remove.dataset.personalAction = 'delete';
    controls.append(edit, remove);
    row.appendChild(controls);
    return row;
  }

  function renderList() {
    const { query, board, items } = currentItems();
    const manual = !query && board.sort === 'manual';
    el.list.replaceChildren(...items.map((item, index) => makeRow(item, index, items.length, Boolean(query), manual)));
    el.sort.value = board.sort;
    el.sort.disabled = Boolean(query);
    const total = allLinks().length;
    el.count.textContent = `${writeLock ? '저장 멈춤 · ' : ''}${query ? `검색 결과 ${items.length}곳 · 전체 ${total}곳` : `${board.links.length}곳`}`;
    el.empty.hidden = items.length > 0;
    el.empty.textContent = query
      ? '검색어와 맞는 내 이음말이 없습니다.'
      : `“${board.title}” 판이 비어 있습니다. 추가 버튼으로 자주 가는 곳을 넣어 보세요.`;
    // 검색 중에는 여러 판의 링크가 섞여 보이므로, 화면에 보이지 않는 활성 판을 지우거나 이름을 바꾸지 못하게 막는다.
    el.deleteBoard.disabled = state.boards.length <= 1 || Boolean(query);
    el.renameBoard.disabled = Boolean(query);
    el.dedupe.disabled = total < 2;
    window.intoSharpApplyFavicons?.(el.list);
  }

  function renderAll() {
    renderTabs();
    renderList();
  }

  // ---------- 판(보드) ----------

  el.tabs.addEventListener('click', event => {
    const tab = event.target.closest('[data-board-id]');
    if (!tab || tab.dataset.boardId === state.activeBoard) return;
    el.search.value = '';
    commit(() => { state.activeBoard = tab.dataset.boardId; }, '', true);
  });

  el.tabs.addEventListener('keydown', event => {
    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
    const tabs = [...el.tabs.querySelectorAll('[data-board-id]')];
    const index = tabs.findIndex(tab => tab === document.activeElement);
    if (index < 0) return;
    event.preventDefault();
    const next = event.key === 'Home' ? 0
      : event.key === 'End' ? tabs.length - 1
        : (index + (event.key === 'ArrowLeft' ? -1 : 1) + tabs.length) % tabs.length;
    tabs[next].click();
    el.tabs.querySelector(`[data-board-id="${CSS.escape(tabs[next].dataset.boardId)}"]`)?.focus();
  });

  el.addBoard.addEventListener('click', () => {
    const title = text(window.prompt('새 판 이름 (예: 월말 정산)', ''), 40);
    if (!title) return;
    const board = { id: makeId('board'), title, sort: 'manual', links: [] };
    commit(() => { state.boards.push(board); state.activeBoard = board.id; });
    showToast(`“${title}” 판을 만들었습니다.`);
  });

  el.renameBoard.addEventListener('click', () => {
    const board = activeBoard();
    const title = text(window.prompt('판 이름', board.title), 40);
    if (!title || title === board.title) return;
    commit(() => { board.title = title; });
  });

  el.deleteBoard.addEventListener('click', () => {
    const board = activeBoard();
    if (state.boards.length <= 1) return;
    if (!window.confirm(`“${board.title}” 판과 안의 링크 ${board.links.length}곳을 지울까요? 잠시 동안 되돌릴 수 있습니다.`)) return;
    commit(() => {
      state.boards = state.boards.filter(item => item.id !== board.id);
      state.activeBoard = state.boards[0].id;
    }, `“${board.title}” 판을 지웠습니다.`);
  });

  el.sort.addEventListener('change', () => {
    const mode = hasSort(el.sort.value) ? el.sort.value : 'manual';
    commit(() => { activeBoard().sort = mode; });
  });

  el.search.addEventListener('input', renderList);
  el.search.addEventListener('keydown', event => {
    if (event.key === 'Escape' && el.search.value) {
      el.search.value = '';
      renderList();
    }
  });

  // ---------- 링크 추가·수정·삭제 ----------

  function fillBoardSelect(selectedId) {
    el.linkBoard.replaceChildren(...state.boards.map(board => {
      const option = document.createElement('option');
      option.value = board.id;
      option.textContent = board.title;
      option.selected = board.id === selectedId;
      return option;
    }));
  }

  function openLinkDialog(link = null, boardId = state.activeBoard) {
    editingLinkId = link?.id || null;
    el.linkTitle.textContent = link ? '내 이음말 수정' : '내 이음말 추가';
    el.linkName.value = link?.name || '';
    el.linkUrl.value = link?.url || '';
    el.linkNote.value = link?.note || '';
    el.linkTodo.value = link?.todo || '';
    el.linkError.textContent = '';
    fillBoardSelect(boardId);
    el.linkDialog.showModal();
    (link ? el.linkName : el.linkUrl).focus();
  }

  el.addLink.addEventListener('click', () => openLinkDialog());
  el.linkCancel.addEventListener('click', () => el.linkDialog.close());

  el.linkForm.addEventListener('submit', event => {
    event.preventDefault();
    const url = normalizeUrl(el.linkUrl.value);
    if (!url) {
      el.linkError.textContent = 'http 또는 https로 시작하는 주소를 입력하세요.';
      el.linkUrl.focus();
      return;
    }
    const name = text(el.linkName.value, NAME_LIMIT) || displayUrl(url).slice(0, NAME_LIMIT);
    const note = text(el.linkNote.value, NOTE_LIMIT);
    const todo = text(el.linkTodo.value, NOTE_LIMIT);
    const targetBoard = state.boards.find(board => board.id === el.linkBoard.value) || activeBoard();
    const ok = commit(() => {
      if (editingLinkId) {
        const found = allLinks().find(({ link }) => link.id === editingLinkId);
        if (!found) return;
        const updated = { ...found.link, name, url, note, todo, done: todo ? found.link.done && todo === found.link.todo : false };
        if (found.board.id === targetBoard.id) {
          found.board.links[found.board.links.indexOf(found.link)] = updated;
        } else {
          found.board.links = found.board.links.filter(link => link.id !== editingLinkId);
          targetBoard.links.push(updated);
        }
        return;
      }
      targetBoard.links.push({ id: makeId('link'), name, url, note, todo, done: false, addedAt: now(), openedAt: 0, openCount: 0 });
      state.activeBoard = targetBoard.id;
    });
    if (!ok) return;
    el.linkDialog.close();
    showToast(editingLinkId ? '수정했습니다.' : `“${targetBoard.title}” 판에 추가했습니다.`);
    editingLinkId = null;
  });

  el.list.addEventListener('click', event => {
    const open = event.target.closest('[data-personal-open]');
    if (open) {
      const found = allLinks().find(({ link }) => link.id === open.dataset.personalOpen);
      if (found) {
        found.link.openedAt = now();
        found.link.openCount += 1;
        persist();
      }
      return;
    }
    const control = event.target.closest('[data-personal-action]');
    if (!control) return;
    const row = control.closest('.personal-row');
    const board = state.boards.find(item => item.id === row.dataset.boardId);
    const index = board?.links.findIndex(link => link.id === row.dataset.linkId) ?? -1;
    if (!board || index < 0) return;
    const link = board.links[index];
    const action = control.dataset.personalAction;

    if (action === 'edit') {
      openLinkDialog(link, board.id);
      return;
    }
    if (action === 'delete') {
      commit(() => { board.links.splice(index, 1); }, `“${link.name}”을 지웠습니다.`);
      return;
    }
    if (action === 'move-up' || action === 'move-down') {
      const destination = index + (action === 'move-up' ? -1 : 1);
      if (destination < 0 || destination >= board.links.length) return;
      commit(() => {
        const moved = board.links.splice(index, 1)[0];
        board.links.splice(destination, 0, moved);
      });
      el.list.querySelector(`[data-link-id="${CSS.escape(link.id)}"] [data-personal-action="${action}"]`)?.focus();
    }
  });

  el.list.addEventListener('change', event => {
    const check = event.target.closest('[data-personal-done]');
    if (!check) return;
    const found = allLinks().find(({ link }) => link.id === check.dataset.personalDone);
    if (!found) return;
    commit(() => { found.link.done = check.checked; });
  });

  // ---------- 백업·복원 ----------

  function download(filename, content, type) {
    try {
      const blob = new Blob([content], { type });
      const url = URL.createObjectURL(blob);
      const anchor = document.createElement('a');
      anchor.href = url;
      anchor.download = filename;
      document.body.appendChild(anchor);
      anchor.click();
      anchor.remove();
      window.setTimeout(() => URL.revokeObjectURL(url), 1000);
      showToast(`${filename} 파일로 내보냈습니다. 안전한 곳에 보관하세요.`);
    } catch (_) {
      showFallbackText(filename, content);
    }
  }

  function showFallbackText(filename, content) {
    const wrap = document.createElement('div');
    wrap.className = 'personal-fallback';
    const note = document.createElement('p');
    note.textContent = `파일 다운로드가 막혀 있습니다. 아래 내용을 복사해 ${filename} 이름으로 저장하세요.`;
    const area = document.createElement('textarea');
    area.readOnly = true;
    area.value = content;
    area.rows = 8;
    area.setAttribute('aria-label', '백업 내용');
    const copy = button('내용 복사', 'admin-control admin-primary');
    copy.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(content);
        showToast('복사했습니다.');
      } catch (_) {
        area.focus();
        area.select();
        showToast('직접 선택해 복사하세요.');
      }
    });
    wrap.append(note, area, copy);
    el.backupBody.querySelector('.personal-fallback')?.remove();
    el.backupBody.appendChild(wrap);
  }

  function stamp() {
    const d = new Date();
    const pad = value => String(value).padStart(2, '0');
    return `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}`;
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
  }

  function exportJson() {
    const payload = { app: 'intosharp-personal', version: 1, exportedAt: new Date().toISOString(), boards: state.boards };
    download(`intosharp-my-links-${stamp()}.json`, JSON.stringify(payload, null, 2), 'application/json');
  }

  function exportHtml() {
    const lines = [
      '<!DOCTYPE NETSCAPE-Bookmark-file-1>',
      '<!-- This is an automatically generated file. It will be read and overwritten. DO NOT EDIT! -->',
      '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">',
      '<TITLE>Bookmarks</TITLE>',
      '<H1>Bookmarks</H1>',
      '<DL><p>',
    ];
    state.boards.forEach(board => {
      lines.push(`    <DT><H3>${escapeHtml(board.title)}</H3>`, '    <DL><p>');
      board.links.forEach(link => {
        const description = [link.note, link.todo ? `할 일: ${link.todo}` : ''].filter(Boolean).join(' / ');
        lines.push(`        <DT><A HREF="${escapeHtml(link.url)}" ADD_DATE="${link.addedAt}">${escapeHtml(link.name)}</A>`);
        if (description) lines.push(`        <DD>${escapeHtml(description)}`);
      });
      lines.push('    </DL><p>');
    });
    lines.push('</DL><p>', '');
    download(`intosharp-bookmarks-${stamp()}.html`, lines.join('\n'), 'text/html');
  }

  function parseImport(name, content) {
    const trimmed = content.trim();
    if (!trimmed) throw new Error('파일이 비어 있습니다.');
    if (trimmed.startsWith('{') || trimmed.startsWith('[')) {
      let data;
      try {
        data = JSON.parse(trimmed);
      } catch (_) {
        throw new Error('JSON 형식이 올바르지 않습니다.');
      }
      const rawBoards = Array.isArray(data) ? data : Array.isArray(data?.boards) ? data.boards : null;
      if (!rawBoards) throw new Error('인투샾 백업 JSON이 아닙니다. boards 목록이 필요합니다.');
      const boards = rawBoards.map((board, index) => cleanBoard(board, index)).filter(Boolean);
      const skipped = rawBoards.reduce((sum, board) => sum + ((Array.isArray(board?.links) ? board.links.length : 0)), 0)
        - boards.reduce((sum, board) => sum + board.links.length, 0);
      return { boards, skipped, kind: 'JSON' };
    }
    if (/<a\s/i.test(trimmed) || /<!doctype netscape-bookmark/i.test(trimmed)) {
      const doc = new DOMParser().parseFromString(trimmed, 'text/html');
      const boards = [];
      let skipped = 0;
      const fallback = { id: makeId('board'), title: text(name.replace(/\.[^.]+$/, ''), 40) || '가져온 이음말', sort: 'manual', links: [] };
      const walk = (dl, boardTitle) => {
        let board = null;
        const ensure = () => {
          if (!board) {
            board = { id: makeId('board'), title: boardTitle, sort: 'manual', links: [] };
            boards.push(board);
          }
          return board;
        };
        [...dl.children].forEach(child => {
          if (child.tagName !== 'DT') return;
          const folder = child.querySelector(':scope > h3');
          const anchor = child.querySelector(':scope > a');
          if (folder) {
            // <DT>를 닫지 않은 파일은 <DL>이 <DT> 안에, </DT>로 닫은 파일은 <DL>이 <DT> 바로 다음 형제로 파싱된다. 둘 다 받는다.
            const sibling = child.nextElementSibling;
            const inner = child.querySelector(':scope > dl') || (sibling?.tagName === 'DL' ? sibling : null);
            if (inner) walk(inner, text(folder.textContent, 40) || '이름 없는 폴더');
            return;
          }
          if (!anchor) return;
          const url = normalizeUrl(anchor.getAttribute('href'));
          if (!url) {
            skipped += 1;
            return;
          }
          const dd = child.nextElementSibling?.tagName === 'DD' ? text(child.nextElementSibling.textContent, NOTE_LIMIT) : '';
          const added = Number(anchor.getAttribute('add_date'));
          ensure().links.push(cleanLink({ name: anchor.textContent, url, note: dd, addedAt: added > 0 ? added : now() }));
        });
      };
      const topLevel = doc.querySelector('dl');
      if (topLevel) walk(topLevel, fallback.title);
      if (boards.length) {
        // 구조 탐색이 놓친 <a>도 건너뛴 수에 넣어, 교체 전에 유실 규모를 알 수 있게 한다.
        const imported = boards.reduce((sum, board) => sum + board.links.length, 0);
        const missed = doc.querySelectorAll('a[href]').length - imported - skipped;
        if (missed > 0) skipped += missed;
      }
      if (!boards.length) {
        doc.querySelectorAll('a[href]').forEach(anchor => {
          const url = normalizeUrl(anchor.getAttribute('href'));
          if (!url) {
            skipped += 1;
            return;
          }
          fallback.links.push(cleanLink({ name: anchor.textContent, url }));
        });
        if (fallback.links.length) boards.push(fallback);
      }
      return { boards, skipped, kind: '북마크 HTML' };
    }
    throw new Error('JSON 또는 북마크 HTML 파일만 가져올 수 있습니다.');
  }

  function renderBackupDialog(preview = null) {
    el.backupBody.replaceChildren();
    const intro = document.createElement('p');
    intro.className = 'personal-dialog-note';
    intro.textContent = '내 이음말은 이 브라우저에만 저장됩니다. 사이트 데이터를 지우거나 브라우저를 초기화하면 사라지므로, 백업 파일을 따로 보관하세요. 개인 링크는 서버로 보내지 않습니다.';
    el.backupBody.appendChild(intro);

    if (writeLock) renderRecovery();

    const exportRow = document.createElement('div');
    exportRow.className = 'personal-dialog-row';
    const json = button('JSON으로 내보내기', 'admin-control admin-primary');
    json.addEventListener('click', exportJson);
    const html = button('북마크 HTML로 내보내기', 'admin-control');
    html.title = '브라우저 북마크 가져오기에서 쓰는 표준 형식';
    html.addEventListener('click', exportHtml);
    exportRow.append(json, html);
    el.backupBody.appendChild(exportRow);

    const importLabel = document.createElement('label');
    importLabel.className = 'personal-file';
    importLabel.textContent = '가져오기 (JSON 또는 북마크 HTML)';
    const file = document.createElement('input');
    file.type = 'file';
    file.accept = '.json,.html,.htm,application/json,text/html';
    file.addEventListener('change', async () => {
      const chosen = file.files?.[0];
      if (!chosen) return;
      try {
        if (chosen.size > 5 * 1024 * 1024) throw new Error('5MB 이하 파일만 가져올 수 있습니다.');
        const parsed = parseImport(chosen.name, await chosen.text());
        renderBackupDialog(parsed);
      } catch (error) {
        renderBackupDialog();
        showToast(error.message || '파일을 읽지 못했습니다.');
      }
    });
    importLabel.appendChild(file);
    el.backupBody.appendChild(importLabel);

    if (preview) renderPreview(preview);

    const dangerRow = document.createElement('div');
    dangerRow.className = 'personal-dialog-row personal-dialog-danger';
    const clear = button('내 이음말 모두 지우기', 'admin-control admin-danger');
    clear.addEventListener('click', () => {
      const total = allLinks().length;
      const lockNote = writeLock ? ' 읽지 못한 저장 원본도 함께 버려집니다.' : '';
      if (!window.confirm(`판 ${state.boards.length}개와 링크 ${total}곳을 모두 지울까요?${lockNote} 먼저 내보내기를 해 두는 것이 안전합니다. 잠시 동안 되돌릴 수 있습니다.`)) return;
      if (writeLock) unlockWrites();
      commit(() => { state = cleanState(null); }, '내 이음말을 모두 지웠습니다.');
      el.backupDialog.close();
    });
    dangerRow.appendChild(clear);
    el.backupBody.appendChild(dangerRow);
  }

  // 저장 원본을 읽지 못해 쓰기를 멈춘 상태의 복구 안내. 원본을 파일로 내보낸 뒤에만 버리도록 유도한다.
  function renderRecovery() {
    const box = document.createElement('div');
    box.className = 'personal-preview';
    const title = document.createElement('h3');
    title.textContent = '읽지 못한 저장 원본';
    const note = document.createElement('p');
    note.textContent = `${writeLock.reason} 원본을 덮어쓰지 않도록 저장을 멈춘 상태입니다. 먼저 원본을 파일로 내보내 두고, 그다음 백업 파일로 전체 교체하거나 새로 시작하세요.`;
    const actions = document.createElement('div');
    actions.className = 'personal-dialog-row';
    const exportRaw = button('원본 그대로 내보내기', 'admin-control admin-primary');
    exportRaw.disabled = !writeLock.raw;
    exportRaw.addEventListener('click', () => download(`intosharp-my-links-raw-${stamp()}.txt`, writeLock.raw, 'text/plain'));
    const discard = button('원본을 버리고 지금 화면 내용으로 새로 시작', 'admin-control admin-danger');
    discard.addEventListener('click', () => {
      if (!window.confirm('읽지 못한 저장 원본을 버리고 지금 화면에 보이는 내용으로 저장할까요? 원본을 아직 내보내지 않았다면 취소하세요.')) return;
      unlockWrites();
      if (persist() === true) showToast('새로 저장했습니다.');
      renderBackupDialog();
      renderAll();
    });
    actions.append(exportRaw, discard);
    box.append(title, note, actions);
    el.backupBody.appendChild(box);
  }

  function renderPreview({ boards, skipped, kind }) {
    const box = document.createElement('div');
    box.className = 'personal-preview';
    const existing = new Set(allLinks().map(({ link }) => urlKey(link.url)));
    const incoming = boards.flatMap(board => board.links);
    const fresh = incoming.filter(link => !existing.has(urlKey(link.url)));
    const title = document.createElement('h3');
    title.textContent = `${kind} 미리보기`;
    const summary = document.createElement('p');
    summary.textContent = `판 ${boards.length}개, 링크 ${incoming.length}곳 · 이미 있는 주소 ${incoming.length - fresh.length}곳 · 건너뛴 항목 ${skipped}개(주소 오류 또는 읽지 못한 구조)`;
    const list = document.createElement('ul');
    boards.forEach(board => {
      const item = document.createElement('li');
      item.textContent = `${board.title}: ${board.links.slice(0, 5).map(link => link.name).join(', ')}${board.links.length > 5 ? ` 외 ${board.links.length - 5}곳` : ''}`;
      list.appendChild(item);
    });
    const actions = document.createElement('div');
    actions.className = 'personal-dialog-row';
    const append = button(`추가하기 (새 주소 ${fresh.length}곳)`, 'admin-control admin-primary');
    append.disabled = !fresh.length;
    append.addEventListener('click', () => {
      commit(() => {
        boards.forEach(board => {
          // 파일 속 ID는 기존 항목과 겹칠 수 있으므로 항상 새로 발급한다.
          const links = board.links.filter(link => !existing.has(urlKey(link.url))).map(link => ({ ...link, id: makeId('link') }));
          if (!links.length) return;
          const target = state.boards.find(item => item.title === board.title);
          if (target) target.links.push(...links);
          else state.boards.push({ ...board, id: makeId('board'), links });
        });
      }, `${fresh.length}곳을 가져왔습니다.`);
      el.backupDialog.close();
    });
    const replace = button('전체 교체', 'admin-control admin-danger');
    replace.disabled = !incoming.length;
    replace.addEventListener('click', () => {
      const lossNote = skipped > 0 ? ` 파일에서 읽지 못한 ${skipped}곳은 들어오지 않습니다.` : '';
      const lockNote = writeLock ? ' 읽지 못한 저장 원본도 함께 버려집니다.' : '';
      if (!window.confirm(`지금 있는 판 ${state.boards.length}개와 링크 ${allLinks().length}곳을 모두 지우고 파일 내용으로 바꿀까요?${lossNote}${lockNote} 잠시 동안 되돌릴 수 있습니다.`)) return;
      if (writeLock) unlockWrites();
      commit(() => { state = cleanState({ boards, activeBoard: boards[0]?.id }); }, '파일 내용으로 교체했습니다.');
      el.backupDialog.close();
    });
    actions.append(append, replace);
    box.append(title, summary, list, actions);
    el.backupBody.appendChild(box);
  }

  el.backup.addEventListener('click', () => {
    renderBackupDialog();
    el.backupDialog.showModal();
  });
  el.backupClose.addEventListener('click', () => el.backupDialog.close());

  // ---------- 중복 정리 ----------

  function renderDedupe() {
    el.dedupeBody.replaceChildren();
    const groups = new Map();
    allLinks().forEach(entry => {
      const key = urlKey(entry.link.url);
      if (!groups.has(key)) groups.set(key, []);
      groups.get(key).push(entry);
    });
    const duplicates = [...groups.values()].filter(group => group.length > 1);
    const note = document.createElement('p');
    note.className = 'personal-dialog-note';
    if (!duplicates.length) {
      note.textContent = '같은 주소로 겹치는 내 이음말이 없습니다.';
      el.dedupeBody.appendChild(note);
      return;
    }
    note.textContent = `같은 주소가 ${duplicates.length}묶음 있습니다. 가장 먼저 넣은 항목을 남기고 나머지를 지우도록 미리 표시했습니다. 지울 항목만 체크하세요.`;
    el.dedupeBody.appendChild(note);
    const form = document.createElement('div');
    duplicates.forEach(group => {
      const sorted = [...group].sort((a, b) => a.link.addedAt - b.link.addedAt);
      const box = document.createElement('fieldset');
      box.className = 'personal-dupe';
      const legend = document.createElement('legend');
      legend.textContent = sorted[0].link.url;
      box.appendChild(legend);
      sorted.forEach(({ board, link }, index) => {
        const label = document.createElement('label');
        const check = document.createElement('input');
        check.type = 'checkbox';
        check.value = link.id;
        check.checked = index > 0;
        const span = document.createElement('span');
        span.textContent = `${board.title} · ${link.name}${link.note ? ` — ${link.note}` : ''}`;
        label.append(check, span);
        box.appendChild(label);
      });
      form.appendChild(box);
    });
    el.dedupeBody.appendChild(form);
    const actions = document.createElement('div');
    actions.className = 'personal-dialog-row';
    const apply = button('체크한 항목 지우기', 'admin-control admin-danger');
    apply.addEventListener('click', () => {
      const ids = new Set([...form.querySelectorAll('input:checked')].map(input => input.value));
      if (!ids.size) {
        showToast('지울 항목을 체크하세요.');
        return;
      }
      commit(() => {
        state.boards.forEach(board => { board.links = board.links.filter(link => !ids.has(link.id)); });
      }, `겹치는 ${ids.size}곳을 지웠습니다.`);
      el.dedupeDialog.close();
    });
    actions.appendChild(apply);
    el.dedupeBody.appendChild(actions);
  }

  el.dedupe.addEventListener('click', () => {
    renderDedupe();
    el.dedupeDialog.showModal();
  });
  el.dedupeClose.addEventListener('click', () => el.dedupeDialog.close());

  // ---------- 검색줄 연동 ----------

  function resolveAlias(name) {
    const normalized = text(name, NAME_LIMIT).toLocaleLowerCase('ko-KR');
    if (!normalized) return null;
    const found = allLinks().find(({ link }) => link.name.toLocaleLowerCase('ko-KR') === normalized);
    if (!found) return null;
    found.link.openedAt = now();
    found.link.openCount += 1;
    persist();
    return found.link.url;
  }

  window.intoSharpPersonal = { resolveAlias };

  window.addEventListener('storage', event => {
    if (event.key !== STORAGE_KEY && event.key !== null) return;
    // 다른 탭의 저장을 받아들이되, 이 탭이 보고 있던 판은 그대로 두고 이전 되돌리기는 무효화한다.
    const currentBoard = state?.activeBoard;
    hideUndo();
    load();
    if (state.boards.some(board => board.id === currentBoard)) state.activeBoard = currentBoard;
    renderAll();
  });

  load();
  renderAll();
})();
