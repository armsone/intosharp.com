(() => {
  'use strict';

  const API_URL = 'admin-api.php';
  const RELEASE_LIFETIME_SECONDS = 7 * 24 * 60 * 60;
  const ACTIVITY_LIMIT = 20;
  const dotTones = ['', 'green', 'blue', 'red', 'violet'];
  const cards = document.querySelector('.cards');
  const filters = document.querySelector('.filters');
  const commandInput = document.getElementById('commandInput');
  const welcomeWords = document.getElementById('welcomeWords');
  const newWords = document.getElementById('newWords');
  const releasedWords = document.getElementById('releasedWords');
  const topActions = document.querySelector('.top-actions');
  const clock = document.getElementById('clock');
  const toast = document.getElementById('toast');
  const loginButton = document.getElementById('adminLoginButton');
  const loginDialog = document.getElementById('adminLoginDialog');
  const loginForm = document.getElementById('adminLoginForm');
  const loginUsername = document.getElementById('adminUsername');
  const loginPassword = document.getElementById('adminPassword');
  const loginError = document.getElementById('adminLoginError');
  const loginCancel = document.getElementById('adminLoginCancel');
  const baseWelcomeItems = [...welcomeWords.querySelectorAll('a')].map(link => ({
    name: link.textContent.trim(),
    url: link.href,
  }));
  const baseNewItems = [...newWords.querySelectorAll('a')].map(link => ({
    name: link.textContent.trim(),
    url: link.href,
    addedAt: 0,
  }));
  const baselineManagedNames = new Set(
    [...cards.querySelectorAll('.link-list > a strong')].map(item => item.textContent.trim().toLocaleLowerCase('ko-KR')),
  );

  let state = null;
  let authenticated = false;
  let dragPayload = null;
  let dragArmed = null;
  let dragTarget = null;
  let suppressClickUntil = 0;

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => toast.classList.remove('show'), 2200);
  }

  async function api(action, payload = {}) {
    const response = await fetch(API_URL, {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'Content-Type': 'application/json', 'X-IntoSharp-Request': '1' },
      body: JSON.stringify({ action, ...payload }),
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.ok) throw new Error(result.error || '요청을 처리하지 못했습니다.');
    return result;
  }

  function makeId(prefix) {
    if (globalThis.crypto?.randomUUID) return `${prefix}-${crypto.randomUUID()}`;
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2)}`;
  }

  function snapshotGroups() {
    return [...cards.querySelectorAll(':scope > .card[data-group]')].map((card, groupIndex) => ({
      id: card.dataset.group || `group-${groupIndex}`,
      title: card.querySelector('.card-title h3')?.textContent.trim() || `묶음 ${groupIndex + 1}`,
      tone: [...(card.querySelector('.dot')?.classList || [])].find(name => dotTones.includes(name) && name !== 'dot') || '',
      sites: [...card.querySelectorAll('.link-list > a')].map((link, siteIndex) => ({
        id: `${card.dataset.group || groupIndex}-site-${siteIndex}`,
        name: link.querySelector('strong')?.textContent.trim() || link.textContent.trim(),
        url: link.href,
        description: link.querySelector('small')?.textContent.trim() || '',
        welcome: baseWelcomeItems.some(item => item.url === link.href || item.name === link.querySelector('strong')?.textContent.trim()),
      })),
    }));
  }

  function initialState() {
    return { version: 1, groups: snapshotGroups(), recent: [], released: [], updatedAt: 0 };
  }

  function cleanupActivity() {
    const cutoff = Math.floor(Date.now() / 1000) - RELEASE_LIFETIME_SECONDS;
    state.recent = (state.recent || []).slice(0, ACTIVITY_LIMIT);
    state.released = (state.released || [])
      .filter(item => Number(item.deletedAt) >= cutoff)
      .slice(0, ACTIVITY_LIMIT);
  }

  function makeMark(name) {
    return Array.from(name.replace(/[^0-9A-Za-z가-힣]/g, '')).slice(0, 2).join('').toUpperCase() || '#';
  }

  function makeSiteLink(site, groupTitle = '') {
    const link = document.createElement('a');
    link.href = site.url;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    const mark = document.createElement('span');
    mark.className = 'site-mark';
    mark.textContent = makeMark(site.name);
    const copy = document.createElement('span');
    copy.className = 'link-copy';
    const strong = document.createElement('strong');
    strong.textContent = site.name;
    const small = document.createElement('small');
    small.textContent = window.intoSharpDescription?.(site.name, groupTitle, site.description) || site.description || '바로가기';
    copy.append(strong, small);
    link.append(mark, copy);
    window.intoSharpApplyFavicons?.(link);
    return link;
  }

  function controlButton(label, action, title = label) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'admin-control';
    button.dataset.adminAction = action;
    button.textContent = label;
    button.title = title;
    button.setAttribute('aria-label', title);
    return button;
  }

  function renderGroups() {
    const activeFilter = filters.querySelector('[data-filter].active')?.dataset.filter || 'all';
    filters.querySelectorAll('[data-filter]:not([data-filter="all"]):not([data-filter="group-search"])').forEach(button => button.remove());
    cards.replaceChildren();

    state.groups.forEach((group, groupIndex) => {
      const filter = document.createElement('button');
      filter.type = 'button';
      filter.dataset.filter = group.id;
      filter.setAttribute('aria-pressed', 'false');
      filter.textContent = group.title.replace(/\s*사이트$/, '');
      filters.appendChild(filter);

      const card = document.createElement('article');
      card.className = 'card';
      card.dataset.group = group.id;
      card.dataset.groupId = group.id;
      const header = document.createElement('header');
      header.className = 'card-head';
      if (authenticated) {
        header.dataset.dragType = 'group';
        header.dataset.groupId = group.id;
        header.draggable = true;
        header.tabIndex = 0;
        header.title = '묶음 머리 부분을 잡아서 이동';
      }
      const titleWrap = document.createElement('div');
      titleWrap.className = 'card-title';
      const dot = document.createElement('span');
      dot.className = `dot${group.tone ? ` ${group.tone}` : ''}`;
      const title = document.createElement('h3');
      if (authenticated) {
        const editTitle = controlButton(group.title, 'group-edit', `${group.title} 이름 수정`);
        editTitle.className = 'group-title-edit';
        editTitle.dataset.groupId = group.id;
        title.appendChild(editTitle);
      } else {
        title.textContent = group.title;
      }
      titleWrap.append(dot, title);
      const count = document.createElement('small');
      count.textContent = `${group.sites.length}곳`;
      header.append(titleWrap, count);

      if (authenticated) {
        const groupControls = document.createElement('div');
        groupControls.className = 'admin-controls group-controls';
        groupControls.dataset.groupId = group.id;
        const addSite = controlButton('＋', 'site-add', `${group.title}에 사이트 추가`);
        const removeGroup = controlButton('×', 'group-delete', `${group.title} 삭제`);
        removeGroup.classList.add('admin-danger');
        groupControls.append(
          addSite,
          removeGroup,
        );
        card.appendChild(groupControls);
      }

      const list = document.createElement('div');
      list.className = 'link-list';
      list.id = group.id;
      group.sites.forEach(site => {
        const link = makeSiteLink(site, group.title);
        if (!authenticated) {
          list.appendChild(link);
          return;
        }
        const row = document.createElement('div');
        row.className = 'admin-site-row';
        row.dataset.groupId = group.id;
        row.dataset.siteId = site.id;
        row.dataset.dragType = 'site';
        row.dataset.adminAction = 'site-edit';
        row.draggable = true;
        row.tabIndex = 0;
        row.title = '한 번 클릭하여 수정 · 패널을 잡아서 이동';
        row.append(link);
        const controls = document.createElement('div');
        controls.className = 'admin-controls site-controls';
        controls.dataset.groupId = group.id;
        controls.dataset.siteId = site.id;
        const welcome = controlButton('★', 'site-welcome', `${site.name}을 마중말에 ${site.welcome ? '내리기' : '올리기'}`);
        welcome.classList.add('admin-star');
        welcome.classList.toggle('active', Boolean(site.welcome));
        const remove = controlButton('×', 'site-delete', `${site.name} 삭제`);
        remove.classList.add('admin-danger');
        controls.append(
          remove,
          welcome,
        );
        row.appendChild(controls);
        list.appendChild(row);
      });
      card.append(header, list);
      cards.appendChild(card);
    });
    if (!authenticated) window.intoSharpApplyFavicons?.(cards);

    const nextActive = filters.querySelector(`[data-filter="${CSS.escape(activeFilter)}"]`) || filters.querySelector('[data-filter="all"]');
    filters.querySelectorAll('[data-filter]').forEach(button => {
      const active = button === nextActive;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    cards.querySelectorAll('[data-group]').forEach(card => {
      card.hidden = nextActive.dataset.filter !== 'all' && card.dataset.group !== nextActive.dataset.filter;
    });
  }

  function renderActivity() {
    cleanupActivity();
    const managedSites = state.groups.flatMap(group => group.sites);
    const promoted = managedSites.filter(site => site.welcome);
    const unmanagedBaseItems = baseWelcomeItems.filter(item => !managedSites.some(site => site.url === item.url || site.name === item.name));
    const welcome = [...promoted, ...unmanagedBaseItems]
      .filter((item, index, list) => list.findIndex(candidate => candidate.name === item.name && candidate.url === item.url) === index);
    welcomeWords.replaceChildren(...welcome.map(item => makeWord(item, false)));
    const recent = [...state.recent, ...baseNewItems]
      .filter((item, index, list) => list.findIndex(candidate => candidate.name === item.name && candidate.url === item.url) === index)
      .slice(0, ACTIVITY_LIMIT);
    newWords.replaceChildren(...recent.map(item => makeWord(item, false)));
    releasedWords.replaceChildren(...state.released.map(item => makeWord(item, true)));
  }

  function makeWord(item, released) {
    const word = document.createElement(released ? 'span' : 'a');
    word.className = 'word-chip';
    word.textContent = item.name;
    if (!released) word.href = item.url;
    if (!released) {
      word.target = '_blank';
      word.rel = 'noopener noreferrer';
    }
    if (released) {
      const expires = new Date((Number(item.deletedAt) + RELEASE_LIFETIME_SECONDS) * 1000);
      word.title = `${expires.toLocaleDateString('ko-KR')}까지 표시`;
    }
    return word;
  }

  function renderAdminChrome() {
    document.body.classList.toggle('admin-mode', authenticated);
    loginButton.hidden = authenticated;
    document.getElementById('adminAccount')?.remove();
    document.getElementById('adminAddGroup')?.remove();
    if (!authenticated) return;
    const account = document.createElement('div');
    account.id = 'adminAccount';
    account.className = 'admin-account';
    const name = document.createElement('strong');
    name.textContent = '임시관리자';
    const logout = controlButton('로그아웃', 'logout');
    logout.className = '';
    account.append(name, logout);
    topActions.insertBefore(account, clock);
    const addGroup = controlButton('묶음 추가', 'group-add');
    addGroup.id = 'adminAddGroup';
    addGroup.classList.add('admin-primary');
    filters.appendChild(addGroup);
  }

  function renderAll() {
    renderGroups();
    renderActivity();
    renderAdminChrome();
  }

  async function saveMutation(change) {
    const previous = JSON.parse(JSON.stringify(state));
    change();
    cleanupActivity();
    renderAll();
    try {
      const result = await api('save', { state });
      state = result.state;
      renderAll();
      showToast('변경 내용을 저장했습니다.');
    } catch (error) {
      state = previous;
      renderAll();
      showToast(error.message);
    }
  }

  function normalizeUrl(raw) {
    const value = raw.trim();
    if (!value) return '';
    const withScheme = /^https?:\/\//i.test(value) ? value : `https://${value}`;
    try {
      const url = new URL(withScheme);
      return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch (_) {
      return '';
    }
  }

  function requestSite(existing = null, groupTitle = '') {
    const name = window.prompt('사이트 이름', existing?.name || '')?.trim();
    if (!name) return null;
    const url = normalizeUrl(window.prompt('사이트 주소', existing?.url || '') || '');
    if (!url) {
      showToast('http 또는 https 주소를 정확히 입력하세요.');
      return null;
    }
    const description = window.intoSharpDescription?.(name, groupTitle, '') || `${name}, 이름으로 바로 이어지는 곳`;
    return {
      id: existing?.id || makeId('site'),
      name: name.slice(0, 100),
      url,
      description: description.slice(0, 120),
      welcome: Boolean(existing?.welcome),
    };
  }

  function groupAndSite(groupId, siteId) {
    const group = state.groups.find(item => item.id === groupId);
    const siteIndex = group?.sites.findIndex(item => item.id === siteId) ?? -1;
    return { group, siteIndex, site: siteIndex >= 0 ? group.sites[siteIndex] : null };
  }

  function addRecent(site) {
    state.recent = [
      { name: site.name, url: site.url, addedAt: Math.floor(Date.now() / 1000) },
      ...state.recent.filter(item => item.name !== site.name && item.url !== site.url),
    ].slice(0, ACTIVITY_LIMIT);
  }

  function addReleased(site) {
    state.recent = state.recent.filter(item => item.name !== site.name && item.url !== site.url);
    state.released = [
      { name: site.name, url: site.url, deletedAt: Math.floor(Date.now() / 1000) },
      ...state.released.filter(item => item.name !== site.name && item.url !== site.url),
    ].slice(0, ACTIVITY_LIMIT);
  }

  async function handleAction(button) {
    const action = button.dataset.adminAction;
    const controls = button.closest('[data-group-id]');
    const groupId = controls?.dataset.groupId;
    const siteId = controls?.dataset.siteId;
    const groupIndex = state.groups.findIndex(group => group.id === groupId);

    if (action === 'logout') {
      await api('logout');
      authenticated = false;
      renderAll();
      commandInput.focus();
      showToast('관리자 모드를 종료했습니다.');
      return;
    }

    if (action === 'group-add') {
      const title = window.prompt('새 묶음 이름')?.trim();
      if (!title) return;
      await saveMutation(() => state.groups.push({ id: makeId('group'), title: title.slice(0, 60), tone: dotTones[state.groups.length % dotTones.length], sites: [] }));
      return;
    }

    if (groupIndex < 0) return;
    const group = state.groups[groupIndex];

    if (action === 'group-edit') {
      const title = window.prompt('묶음 이름', group.title)?.trim();
      if (title && title !== group.title) await saveMutation(() => { group.title = title.slice(0, 60); });
      return;
    }
    if (action === 'group-delete') {
      if (!window.confirm(`“${group.title}” 묶음과 안의 사이트 ${group.sites.length}개를 삭제할까요?`)) return;
      await saveMutation(() => {
        group.sites.forEach(addReleased);
        state.groups.splice(groupIndex, 1);
      });
      return;
    }
    if (action === 'site-add') {
      const site = requestSite(null, group.title);
      if (!site) return;
      await saveMutation(() => { group.sites.push(site); addRecent(site); });
      return;
    }

    const found = groupAndSite(groupId, siteId);
    if (!found.site) return;

    if (action === 'site-welcome') {
      await saveMutation(() => { found.site.welcome = !found.site.welcome; });
      return;
    }
    if (action === 'site-edit') {
      const edited = requestSite(found.site, group.title);
      if (!edited) return;
      await saveMutation(() => {
        const recentItem = state.recent.find(item => item.name === found.site.name && item.url === found.site.url);
        group.sites[found.siteIndex] = edited;
        if (recentItem) {
          recentItem.name = edited.name;
          recentItem.url = edited.url;
        }
      });
      return;
    }
    if (action === 'site-delete') {
      if (!window.confirm(`“${found.site.name}” 사이트를 삭제할까요?`)) return;
      await saveMutation(() => { group.sites.splice(found.siteIndex, 1); addReleased(found.site); });
      return;
    }
  }

  function clearDragState() {
    document.querySelectorAll('.admin-drop-target').forEach(item => item.classList.remove('admin-drop-target'));
    document.querySelectorAll('.admin-mode [aria-grabbed]').forEach(item => item.removeAttribute('aria-grabbed'));
    dragPayload = null;
    dragArmed = null;
    dragTarget = null;
  }

  function beginDrag(event) {
    if (!authenticated) return;
    const handle = event.target.closest('[data-drag-type]');
    if (!handle) return;
    if (event.target.closest('.site-controls, .group-controls')) return;
    const draggedElement = handle.dataset.dragType === 'group'
      ? handle.closest('.card')
      : handle.closest('.admin-site-row');
    if (!draggedElement) return;
    dragArmed = {
      type: handle.dataset.dragType,
      groupId: handle.dataset.groupId,
      siteId: handle.dataset.siteId || '',
      startX: event.clientX,
      startY: event.clientY,
      element: draggedElement,
    };
    if (Number.isInteger(event.pointerId)) handle.setPointerCapture?.(event.pointerId);
  }

  function moveDrag(event) {
    if (!authenticated || !dragArmed) return;
    const distance = Math.hypot(event.clientX - dragArmed.startX, event.clientY - dragArmed.startY);
    if (!dragPayload && distance < 6) return;
    event.preventDefault();
    if (!dragPayload) {
      dragPayload = { type: dragArmed.type, groupId: dragArmed.groupId, siteId: dragArmed.siteId };
      dragArmed.element.setAttribute('aria-grabbed', 'true');
    }
    const hit = document.elementFromPoint(event.clientX, event.clientY);
    updateDragTarget(hit, event.clientX, event.clientY);
  }

  function updateDragTarget(hit, clientX, clientY) {
    let target = dragPayload.type === 'group'
      ? hit?.closest('.card[data-group-id]')
      : hit?.closest('.admin-site-row, .card[data-group-id]');
    if (dragPayload.type === 'site' && (!target || target.matches('.card') || target.dataset.siteId === dragPayload.siteId)) {
      target = [...document.querySelectorAll('.admin-site-row')].find(row => {
        if (row.dataset.siteId === dragPayload.siteId) return false;
        const rect = row.getBoundingClientRect();
        return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
      }) || target;
    }
    document.querySelectorAll('.admin-drop-target').forEach(item => item.classList.remove('admin-drop-target'));
    dragTarget = null;
    if (!target) return;
    if (dragPayload.type === 'group' && target.dataset.groupId === dragPayload.groupId) return;
    if (dragPayload.type === 'site' && target.dataset.siteId === dragPayload.siteId) return;
    target.classList.add('admin-drop-target');
    dragTarget = target;
  }

  function finishDrag(event) {
    if (dragPayload) suppressClickUntil = Date.now() + 500;
    if (!dragPayload || !dragTarget) {
      clearDragState();
      return;
    }
    const payload = { ...dragPayload };
    const targetCard = dragTarget.closest('.card[data-group-id]');
    const targetRow = payload.type === 'site' ? dragTarget.closest('.admin-site-row') : null;
    if (!targetCard) {
      clearDragState();
      return;
    }
    const targetRect = (targetRow || targetCard).getBoundingClientRect();
    const placeAfter = event.clientY > targetRect.top + targetRect.height / 2;
    clearDragState();

    if (payload.type === 'group') {
      if (payload.groupId === targetCard.dataset.groupId) return;
      saveMutation(() => {
        const sourceIndex = state.groups.findIndex(group => group.id === payload.groupId);
        if (sourceIndex < 0) return;
        const moved = state.groups.splice(sourceIndex, 1)[0];
        const targetIndex = state.groups.findIndex(group => group.id === targetCard.dataset.groupId);
        state.groups.splice(targetIndex + (placeAfter ? 1 : 0), 0, moved);
      }).catch(error => showToast(error.message));
      return;
    }

    const sourceGroup = state.groups.find(group => group.id === payload.groupId);
    const sourceIndex = sourceGroup?.sites.findIndex(site => site.id === payload.siteId) ?? -1;
    if (!sourceGroup || sourceIndex < 0 || targetRow?.dataset.siteId === payload.siteId) return;
    saveMutation(() => {
      const destination = state.groups.find(group => group.id === targetCard.dataset.groupId);
      if (!destination) return;
      const targetOriginalIndex = targetRow ? destination.sites.findIndex(site => site.id === targetRow.dataset.siteId) : -1;
      const movingForwardInGroup = sourceGroup === destination && sourceIndex < targetOriginalIndex;
      const moved = sourceGroup.sites.splice(sourceIndex, 1)[0];
      if (!targetRow) {
        destination.sites.push(moved);
        return;
      }
      const targetIndex = destination.sites.findIndex(site => site.id === targetRow.dataset.siteId);
      const insertAfter = sourceGroup === destination ? movingForwardInGroup : placeAfter;
      destination.sites.splice(targetIndex + (insertAfter ? 1 : 0), 0, moved);
    }).catch(error => showToast(error.message));
  }

  document.addEventListener('pointerdown', beginDrag, { capture: true });
  document.addEventListener('pointermove', moveDrag);
  document.addEventListener('pointerup', finishDrag);
  document.addEventListener('pointercancel', clearDragState);
  document.addEventListener('mousedown', beginDrag, { capture: true });
  document.addEventListener('mousemove', moveDrag);
  document.addEventListener('mouseup', finishDrag);

  document.addEventListener('keydown', event => {
    if (!authenticated || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
    const handle = event.target.closest('[data-drag-type]');
    if (!handle || event.target !== handle) return;
    event.preventDefault();
    const offset = event.key === 'ArrowUp' ? -1 : 1;
    if (handle.dataset.dragType === 'group') {
      const index = state.groups.findIndex(group => group.id === handle.dataset.groupId);
      const destination = index + offset;
      if (index < 0 || destination < 0 || destination >= state.groups.length) return;
      saveMutation(() => {
        const moved = state.groups.splice(index, 1)[0];
        state.groups.splice(destination, 0, moved);
      }).catch(error => showToast(error.message));
      return;
    }
    const group = state.groups.find(item => item.id === handle.dataset.groupId);
    const index = group?.sites.findIndex(site => site.id === handle.dataset.siteId) ?? -1;
    const destination = index + offset;
    if (!group || index < 0 || destination < 0 || destination >= group.sites.length) return;
    saveMutation(() => {
      const moved = group.sites.splice(index, 1)[0];
      group.sites.splice(destination, 0, moved);
    }).catch(error => showToast(error.message));
  });

  filters.addEventListener('click', event => {
    const button = event.target.closest('[data-filter]');
    if (!button) return;
    filters.querySelectorAll('[data-filter]').forEach(item => {
      const active = item === button;
      item.classList.toggle('active', active);
      item.setAttribute('aria-pressed', String(active));
    });
    document.querySelectorAll('[data-group]').forEach(card => {
      card.hidden = button.dataset.filter !== 'all' && card.dataset.group !== button.dataset.filter;
    });
  });

  document.addEventListener('click', event => {
    const button = event.target.closest('[data-admin-action]');
    if (!button || !authenticated) return;
    if (Date.now() < suppressClickUntil) {
      event.preventDefault();
      event.stopPropagation();
      suppressClickUntil = 0;
      return;
    }
    event.preventDefault();
    handleAction(button).catch(error => showToast(error.message));
  });

  async function login() {
    loginError.textContent = '';
    const submit = loginForm.querySelector('[type="submit"]');
    submit.disabled = true;
    try {
      const result = await api('login', {
        username: loginUsername.value,
        password: loginPassword.value,
      });
      authenticated = result.authenticated;
      loginPassword.value = '';
      loginDialog.close();
      if (!state) state = initialState();
      renderAll();
      showToast('관리자로 로그인했습니다.');
    } catch (error) {
      loginPassword.value = '';
      loginError.textContent = error.message;
      loginPassword.focus();
    } finally {
      submit.disabled = false;
    }
  }

  function resolveAlias(name) {
    if (!state) return null;
    const normalized = name.trim().toLocaleLowerCase('ko-KR');
    const site = state.groups.flatMap(group => group.sites).find(item => item.name.toLocaleLowerCase('ko-KR') === normalized);
    if (site) return { managed: true, url: site.url };
    if (baselineManagedNames.has(normalized)) return { managed: true, url: null };
    return null;
  }

  loginButton.addEventListener('click', () => {
    loginError.textContent = '';
    loginDialog.showModal();
    loginUsername.focus();
  });
  loginCancel.addEventListener('click', () => loginDialog.close());
  loginForm.addEventListener('submit', event => {
    event.preventDefault();
    login().catch(error => {
      loginError.textContent = error.message;
    });
  });

  window.intoSharpAdmin = { resolveAlias };

  async function init() {
    try {
      const result = await api('load');
      authenticated = Boolean(result.authenticated);
      state = result.state || initialState();
      renderAll();
    } catch (_) {
      state = initialState();
      renderActivity();
    }
  }

  init();
})();
