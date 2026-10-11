// Waspada — options.js (Manifest V3)
// Menyimpan API base URL ke chrome.storage.sync

const DEFAULT_API_BASE = "http://localhost:8000";

document.addEventListener("DOMContentLoaded", async () => {
  const input = document.getElementById("apiBase");
  const saveBtn = document.getElementById("saveBtn");
  const status = document.getElementById("status");

  const stored = await chrome.storage.sync.get("apiBase");
  input.value = stored.apiBase || DEFAULT_API_BASE;

  saveBtn.addEventListener("click", async () => {
    let value = input.value.trim().replace(/\/+$/, "");
    if (!value) {
      value = DEFAULT_API_BASE;
    }
    if (!/^https?:\/\//i.test(value)) {
      status.style.color = "#b91c1c";
      status.textContent = "URL harus diawali http:// atau https://";
      return;
    }
    await chrome.storage.sync.set({ apiBase: value });
    input.value = value;
    status.style.color = "#059669";
    status.textContent = "Tersimpan! Ekstensi akan memakai: " + value;
    setTimeout(() => { status.textContent = ""; }, 3000);
  });
});
