const shortcuts = {
  "네이버": { url: "https://www.naver.com/", search: "https://search.naver.com/search.naver?query=" },
  "naver": { url: "https://www.naver.com/", search: "https://search.naver.com/search.naver?query=" },
  "구글": { url: "https://www.google.com/", search: "https://www.google.com/search?q=" },
  "google": { url: "https://www.google.com/", search: "https://www.google.com/search?q=" },
  "유튜브": { url: "https://www.youtube.com/?gl=KR", search: "https://www.youtube.com/results?search_query=" },
  "youtube": { url: "https://www.youtube.com/?gl=KR", search: "https://www.youtube.com/results?search_query=" },
  "지도": { url: "https://map.naver.com/", search: "https://map.naver.com/p/search/" },
  "네이버지도": { url: "https://map.naver.com/", search: "https://map.naver.com/p/search/" },
  "쇼핑": { url: "https://shopping.naver.com/", search: "https://search.shopping.naver.com/search/all?query=" },
  "ChatGPT": { url: "https://chatgpt.com/" },
  "챗지피티": { url: "https://chatgpt.com/" },
  "제미나이": { url: "https://gemini.google.com/" },
  "gemini": { url: "https://gemini.google.com/" },
  "텔레그램": { url: "https://web.telegram.org/" },
  "클리앙": { url: "https://www.clien.net/service/", search: "https://www.google.com/search?q=site%3Aclien.net+" },
  "넷플릭스": { url: "https://www.netflix.com/kr/" },
  "한통": { url: "https://smartstore.naver.com/hantongbox" },
  "한통스토어": { url: "https://smartstore.naver.com/hantongbox" },
  "스마트스토어": { url: "https://sell.smartstore.naver.com/" },
  "톡톡": { url: "https://partner.talk.naver.com/" },
  "페이앱": { url: "https://payapp.kr/" },
  "유니포스트": { url: "https://unipost.co.kr/" },
  "아이파킹": { url: "https://members.iparking.co.kr/" },
  "앱스토어": { url: "https://appstoreconnect.apple.com/" },
  "깃허브": { url: "https://github.com/" },
  "아이클라우드": { url: "https://www.icloud.com/" },
  "피들리": { url: "https://feedly.com/" }
};

const form = document.querySelector("#searchForm");
const input = document.querySelector("#commandInput");
const greeting = document.querySelector("#greeting");
const clock = document.querySelector("#clock");
const themeToggle = document.querySelector("#themeToggle");
const themeColor = document.querySelector('meta[name="theme-color"]');

function resolveCommand(raw) {
  const value = raw.trim().replace(/\s+/g, " ");
  if (!value) return null;

  const lowerValue = value.toLocaleLowerCase("ko-KR");
  const match = Object.entries(shortcuts)
    .sort((a, b) => b[0].length - a[0].length)
    .find(([alias]) => {
      const normalizedAlias = alias.toLocaleLowerCase("ko-KR");
      return lowerValue === normalizedAlias || lowerValue.startsWith(`${normalizedAlias} `);
    });

  if (!match) {
    if (/^(https?:\/\/|[\w-]+\.[a-z]{2,})(\/|$)/i.test(value)) {
      return value.startsWith("http") ? value : `https://${value}`;
    }
    return `https://search.naver.com/search.naver?query=${encodeURIComponent(value)}`;
  }

  const [alias, target] = match;
  const query = value.slice(alias.length).trim();
  if (!query) return target.url;
  if (target.search) return `${target.search}${encodeURIComponent(query)}`;
  return `https://search.naver.com/search.naver?query=${encodeURIComponent(query)}`;
}

form.addEventListener("submit", (event) => {
  event.preventDefault();
  const destination = resolveCommand(input.value);
  if (destination) window.location.assign(destination);
});

document.querySelectorAll("[data-example]").forEach((button) => {
  button.addEventListener("click", () => {
    input.value = button.dataset.example;
    input.focus();
  });
});

document.querySelectorAll("[data-filter]").forEach((button) => {
  button.addEventListener("click", () => {
    const selected = button.dataset.filter;
    document.querySelectorAll("[data-filter]").forEach((item) => {
      const active = item === button;
      item.classList.toggle("active", active);
      item.setAttribute("aria-pressed", String(active));
    });
    document.querySelectorAll(".group").forEach((group) => {
      group.hidden = selected !== "all" && group.dataset.category !== selected;
    });
  });
});

function updateTime() {
  const now = new Date();
  const hour = now.getHours();
  const greetingText = hour < 5 ? "고요한 새벽이에요." : hour < 11 ? "좋은 아침이에요." : hour < 17 ? "좋은 오후예요." : hour < 22 ? "좋은 저녁이에요." : "편안한 밤이에요.";
  greeting.textContent = greetingText;
  clock.textContent = new Intl.DateTimeFormat("ko-KR", { month: "long", day: "numeric", weekday: "short", hour: "2-digit", minute: "2-digit" }).format(now);
}

function applyTheme(theme) {
  document.documentElement.dataset.theme = theme;
  const dark = theme === "dark";
  themeToggle.setAttribute("aria-label", dark ? "밝은 화면으로 전환" : "어두운 화면으로 전환");
  themeColor.setAttribute("content", dark ? "#111513" : "#f5f1e8");
}

const savedTheme = localStorage.getItem("intosharp-theme");
applyTheme(savedTheme || (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"));
themeToggle.addEventListener("click", () => {
  const next = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
  applyTheme(next);
  localStorage.setItem("intosharp-theme", next);
});

updateTime();
setInterval(updateTime, 30_000);
