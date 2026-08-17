(() => {
  'use strict';

  document.documentElement.classList.add('modern-ready');

  if (!('serviceWorker' in navigator) || location.protocol !== 'https:') return;

  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' })
      .catch(() => {
        // 오프라인 지원 실패는 검색과 바로가기의 정상 동작을 막지 않는다.
      });
  }, { once: true });
})();
