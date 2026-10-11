// Waspada — popup.js (Manifest V3)
// Mengecek URL tab aktif ke endpoint POST {apiBase}/api/check

const DEFAULT_API_BASE = "http://localhost:8000";
const TIMEOUT_MS = 15000;

const VERDICT_META = {
  "aman":      { label: "🟢 AMAN",      cls: "aman" },
  "hati-hati": { label: "🟡 HATI-HATI", cls: "hati-hati" },
  "bahaya":    { label: "🔴 BAHAYA",    cls: "bahaya" },
};

document.addEventListener("DOMContentLoaded", async () => {
  const urlEl = document.getElementById("currentUrl");
  const checkBtn = document.getElementById("checkBtn");
  const resultEl = document.getElementById("result");
  const badgeEl = document.getElementById("verdictBadge");
  const reasonsEl = document.getElementById("reasonsList");
  const errorEl = document.getElementById("error");

  // Tampilkan URL tab aktif (atau URL pending dari context menu)
  let tabUrl = "";
  try {
    const stored = await chrome.storage.local.get("pendingUrl");
    if (stored.pendingUrl) {
      tabUrl = stored.pendingUrl;
      await chrome.storage.local.remove("pendingUrl");
    } else {
      const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
      tabUrl = tab && tab.url ? tab.url : "";
    }
  } catch (e) {
    tabUrl = "";
  }

  if (!tabUrl || tabUrl.startsWith("chrome://") || tabUrl.startsWith("edge://") || tabUrl.startsWith("about:")) {
    urlEl.textContent = tabUrl ? "Halaman internal browser tidak bisa dicek." : "Tidak ada URL yang bisa dicek.";
    checkBtn.disabled = true;
    return;
  }
  urlEl.textContent = tabUrl;
  urlEl.title = tabUrl;

  document.getElementById("openOptions").addEventListener("click", (e) => {
    e.preventDefault();
    chrome.runtime.openOptionsPage();
  });

  checkBtn.addEventListener("click", async () => {
    hideError();
    hideResult();
    checkBtn.disabled = true;
    checkBtn.textContent = "Mengecek…";

    try {
      const { apiBase } = await chrome.storage.sync.get("apiBase");
      const base = (apiBase || DEFAULT_API_BASE).replace(/\/+$/, "");
      const data = await postCheck(base, tabUrl);
      showResult(data);
    } catch (err) {
      showError(err.message || "Terjadi kesalahan yang tidak diketahui.");
    } finally {
      checkBtn.disabled = false;
      checkBtn.textContent = "Cek Keamanan";
    }
  });

  async function postCheck(base, value) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), TIMEOUT_MS);
    try {
      const res = await fetch(`${base}/api/check`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({ type: "url", value }),
        signal: controller.signal,
      });
      if (!res.ok) {
        throw new Error(`Server menjawab dengan status ${res.status}. Periksa kembali API base URL di Opsi.`);
      }
      return await res.json();
    } catch (err) {
      if (err.name === "AbortError") {
        throw new Error("Permintaan habis waktu (15 detik). Server Waspada mungkin lambat atau tidak merespons.");
      }
      if (err instanceof TypeError) {
        throw new Error("Tidak dapat menghubungi server Waspada. Periksa API base URL di Opsi.");
      }
      throw err;
    } finally {
      clearTimeout(timer);
    }
  }

  function showResult(data) {
    // API mengembalikan {data: {verdict, explanation, ...}} — normalisasi dulu.
    const payload = (data && data.data) || {};
    const meta = VERDICT_META[payload.verdict] || { label: String(payload.verdict || "?").toUpperCase(), cls: "" };
    badgeEl.textContent = meta.label;
    badgeEl.className = "badge " + meta.cls;

    reasonsEl.innerHTML = "";
    const reasons = Array.isArray(payload.reasons)
      ? payload.reasons
      : (payload.explanation ? [payload.explanation] : []);
    if (reasons.length === 0) {
      const li = document.createElement("li");
      li.textContent = "Tidak ada alasan tambahan dari server.";
      reasonsEl.appendChild(li);
    } else {
      for (const r of reasons) {
        const li = document.createElement("li");
        li.textContent = String(r);
        reasonsEl.appendChild(li);
      }
    }
    resultEl.classList.remove("hidden");
  }

  function hideResult() {
    resultEl.classList.add("hidden");
  }

  function showError(msg) {
    errorEl.textContent = msg;
    errorEl.classList.remove("hidden");
  }

  function hideError() {
    errorEl.classList.add("hidden");
    errorEl.textContent = "";
  }
});
