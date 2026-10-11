// Waspada — background.js (Manifest V3 service worker)
// Menambahkan item context menu "Cek dengan Waspada" untuk link & halaman.

const MENU_ID = "waspada-check";

chrome.runtime.onInstalled.addListener(() => {
  chrome.contextMenus.create({
    id: MENU_ID,
    title: "Cek dengan Waspada",
    contexts: ["link", "page"],
  });
});

// Service worker bisa dihentikan kapan saja; pastikan menu ada setiap startup.
chrome.runtime.onStartup.addListener(() => {
  chrome.contextMenus.removeAll(() => {
    chrome.contextMenus.create({
      id: MENU_ID,
      title: "Cek dengan Waspada",
      contexts: ["link", "page"],
    });
  });
});

chrome.contextMenus.onClicked.addListener(async (info, tab) => {
  if (info.menuItemId !== MENU_ID) return;

  // URL yang dicek: link yang diklik-kanan, atau URL halaman itu sendiri.
  const targetUrl = info.linkUrl || info.pageUrl || (tab && tab.url) || "";
  if (!targetUrl) return;

  // Simpan untuk dibaca popup, lalu buka popup lewat action.
  // (Catatan: chrome.action.openPopup() hanya bisa dipanggil dari user gesture
  //  yang didukung; fallback: buka popup.html di tab baru.)
  try {
    await chrome.storage.local.set({ pendingUrl: targetUrl });
    if (tab && tab.windowId !== undefined) {
      await chrome.action.openPopup({ windowId: tab.windowId });
      return;
    }
  } catch (e) {
    // openPopup tidak tersedia di konteks ini — lanjut ke fallback.
  }
  chrome.tabs.create({ url: chrome.runtime.getURL("popup.html") });
});
