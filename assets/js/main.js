/**
 * Main JavaScript — Utilities & Core Components
 * Little Salt Bread Blok M — POS System
 *
 * Core utilities: Fetch wrapper, Cart Manager, Toast System,
 * Sound Notifications, Rupiah formatter, and CSRF handling.
 */
"use strict";

// ═══════════════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════

/**
 * Format number to Indonesian Rupiah
 * @param {number} amount
 * @returns {string} "Rp 28.000"
 */
function formatRupiah(amount) {
  const num = parseInt(amount) || 0;
  return "Rp " + num.toLocaleString("id-ID");
}

/**
 * Format relative time (e.g., "5 menit lalu")
 * @param {string} dateStr ISO date string
 * @returns {string}
 */
function timeAgo(dateStr) {
  const date = new Date(dateStr);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return "Baru saja";
  if (diffMins < 60) return `${diffMins} menit lalu`;
  if (diffHours < 24) return `${diffHours} jam lalu`;
  if (diffDays < 7) return `${diffDays} hari lalu`;
  return date.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

/**
 * Debounce function
 * @param {Function} fn
 * @param {number} delay ms
 * @returns {Function}
 */
function debounce(fn, delay = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ═══════════════════════════════════════════════════════════════════
// CSRF TOKEN
// ═══════════════════════════════════════════════════════════════════

/**
 * Get CSRF token from meta tag or hidden input
 * @returns {string|null}
 */
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) return meta.content;

  const input = document.querySelector('input[name="csrf_token"]');
  if (input) return input.value;

  return null;
}

// ═══════════════════════════════════════════════════════════════════
// FETCH WRAPPER
// ═══════════════════════════════════════════════════════════════════

/**
 * Fetch JSON with automatic error handling and CSRF injection
 * @param {string} url API endpoint
 * @param {object} options Fetch options
 * @returns {Promise<object>} Parsed JSON response
 */
async function fetchJSON(url, options = {}) {
  const defaults = {
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    credentials: "same-origin",
  };

  // Merge headers
  const mergedOptions = {
    ...defaults,
    ...options,
    headers: {
      ...defaults.headers,
      ...(options.headers || {}),
    },
  };

  // Inject CSRF token for mutating requests
  if (
    ["POST", "PUT", "PATCH", "DELETE"].includes(
      (mergedOptions.method || "GET").toUpperCase(),
    )
  ) {
    const csrf = getCsrfToken();
    if (csrf) {
      if (mergedOptions.body && typeof mergedOptions.body === "string") {
        try {
          const bodyObj = JSON.parse(mergedOptions.body);
          bodyObj.csrf_token = csrf;
          mergedOptions.body = JSON.stringify(bodyObj);
        } catch {
          // body is not JSON, skip
        }
      }
    }
  }

  try {
    const response = await fetch(url, mergedOptions);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || `HTTP Error ${response.status}`);
    }

    return data;
  } catch (error) {
    if (error instanceof SyntaxError) {
      throw new Error("Server mengembalikan respons yang tidak valid.");
    }
    throw error;
  }
}

/**
 * POST JSON helper
 * @param {string} url
 * @param {object} body
 * @returns {Promise<object>}
 */
async function postJSON(url, body = {}) {
  return fetchJSON(url, {
    method: "POST",
    body: JSON.stringify(body),
  });
}

// ═══════════════════════════════════════════════════════════════════
// TOAST NOTIFICATION SYSTEM
// ═══════════════════════════════════════════════════════════════════

const Toast = {
  container: null,

  /**
   * Initialize toast container
   */
  init() {
    if (this.container) return;
    this.container = document.createElement("div");
    this.container.className = "toast-container";
    this.container.setAttribute("aria-live", "polite");
    this.container.setAttribute("role", "status");
    document.body.appendChild(this.container);
  },

  /**
   * Show a toast notification
   * @param {string} message
   * @param {'success'|'error'|'warning'|'info'} type
   * @param {number} duration ms (0 = persistent)
   */
  show(message, type = "info", duration = 2000) {
    this.init();

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const icons = {
      success: `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`,
      error: `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>`,
      warning: `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`,
      info: `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`,
    };

    toast.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <span class="toast-message">${message}</span>
      <button class="toast-close" aria-label="Tutup">&times;</button>
    `;

    toast.querySelector(".toast-close").addEventListener("click", (e) => {
      e.stopPropagation();
      this.dismiss(toast);
    });

    this.container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => {
      toast.classList.add("toast-show");
    });

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => this.dismiss(toast), duration);
    }

    return toast;
  },

  /**
   * Dismiss a toast with animation
   * @param {HTMLElement} toast
   */
  dismiss(toast) {
    if (!toast || !toast.parentNode) return;
    toast.classList.add("toast-hide");
    toast.addEventListener(
      "animationend",
      () => {
        toast.remove();
      },
      { once: true },
    );
    // Fallback removal
    setTimeout(() => toast.remove(), 500);
  },

  success(msg, duration) {
    return this.show(msg, "success", duration);
  },
  error(msg, duration) {
    return this.show(msg, "error", duration);
  },
  warning(msg, duration) {
    return this.show(msg, "warning", duration);
  },
  info(msg, duration) {
    return this.show(msg, "info", duration);
  },
};

// ═══════════════════════════════════════════════════════════════════
// CART MANAGER (localStorage-based)
// ═══════════════════════════════════════════════════════════════════

class CartManager {
  constructor() {
    this.STORAGE_KEY = "sb_cart";
    this.listeners = [];
  }

  /**
   * Get all cart items
   * @returns {Array<{id: number, name: string, price: number, quantity: number, image: string, options: object}>}
   */
  getItems() {
    try {
      const data = localStorage.getItem(this.STORAGE_KEY);
      return data ? JSON.parse(data) : [];
    } catch {
      return [];
    }
  }

  /**
   * Save items to localStorage and notify listeners
   * @param {Array} items
   */
  _save(items) {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(items));
    this._notify();
  }

  /**
   * Add item to cart or increment quantity
   * @param {object} product {id, name, price, image, options}
   * @param {number} quantity
   */
  addItem(product, quantity = 1) {
    const now = Date.now();
    const itemKey = `${product.id}_${JSON.stringify(product.options || {})}`;
    if (this._lastAddKey === itemKey && now - (this._lastAddTime || 0) < 450) {
      return;
    }
    this._lastAddKey = itemKey;
    this._lastAddTime = now;

    const qty = Math.max(1, parseInt(quantity) || 1);
    const optionsKey = JSON.stringify(product.options || {});
    const items = this.getItems();

    const existingIdx = items.findIndex(
      (item) =>
        item.id === product.id &&
        JSON.stringify(item.options || {}) === optionsKey,
    );

    if (existingIdx >= 0) {
      items[existingIdx].quantity += qty;
    } else {
      items.push({
        id: product.id,
        name: product.name,
        price: parseInt(product.price),
        image: product.image || "",
        quantity: qty,
        options: product.options || {},
      });
    }

    this._save(items);
    Toast.success(`${product.name} ditambahkan`);
  }

  /**
   * Update item quantity
   * @param {number} index Cart item index
   * @param {number} quantity New quantity
   */
  updateQuantity(index, quantity) {
    const items = this.getItems();
    if (index < 0 || index >= items.length) return;

    if (quantity <= 0) {
      items.splice(index, 1);
    } else {
      items[index].quantity = quantity;
    }

    this._save(items);
  }

  /**
   * Remove item from cart
   * @param {number} index
   */
  removeItem(index) {
    const items = this.getItems();
    if (index < 0 || index >= items.length) return;

    const removed = items.splice(index, 1)[0];
    this._save(items);
    Toast.info(`${removed.name} dihapus`);
  }

  /**
   * Clear all items from cart
   */
  clear() {
    localStorage.removeItem(this.STORAGE_KEY);
    this._notify();
  }

  /**
   * Get total number of items in cart
   * @returns {number}
   */
  getTotalItems() {
    return this.getItems().reduce((sum, item) => sum + item.quantity, 0);
  }

  /**
   * Get subtotal price
   * @returns {number}
   */
  getSubtotal() {
    return this.getItems().reduce(
      (sum, item) => sum + item.price * item.quantity,
      0,
    );
  }

  /**
   * Check if cart is empty
   * @returns {boolean}
   */
  isEmpty() {
    return this.getItems().length === 0;
  }

  /**
   * Subscribe to cart changes
   * @param {Function} callback
   * @returns {Function} Unsubscribe function
   */
  onChange(callback) {
    this.listeners.push(callback);
    return () => {
      this.listeners = this.listeners.filter((cb) => cb !== callback);
    };
  }

  /**
   * Notify all listeners of cart changes
   */
  _notify() {
    const items = this.getItems();
    const totalItems = this.getTotalItems();
    const subtotal = this.getSubtotal();

    this.listeners.forEach((cb) => cb({ items, totalItems, subtotal }));
  }
}

// Global cart instance
const cart = new CartManager();
window.cart = cart;

// ═══════════════════════════════════════════════════════════════════
// SOUND NOTIFIER (Web Audio API)
// ═══════════════════════════════════════════════════════════════════

class SoundNotifier {
  constructor() {
    this.audioCtx = null;
    this.enabled = true;
  }

  /**
   * Initialize AudioContext (must be called from user interaction)
   */
  init() {
    if (!this.audioCtx) {
      this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return this;
  }

  /**
   * Play a notification chime (dual-tone bell)
   * @param {'order'|'ready'|'alert'} type
   */
  play(type = "order") {
    if (!this.enabled || !this.audioCtx) return;

    const tones = {
      order: [
        { freq: 659.25, duration: 0.15 },
        { freq: 783.99, duration: 0.2 },
      ],
      ready: [
        { freq: 783.99, duration: 0.15 },
        { freq: 987.77, duration: 0.15 },
        { freq: 1174.66, duration: 0.25 },
      ],
      alert: [
        { freq: 440, duration: 0.3 },
        { freq: 440, duration: 0.3 },
      ],
    };

    const sequence = tones[type] || tones.order;
    let startTime = this.audioCtx.currentTime;

    sequence.forEach(({ freq, duration }) => {
      const oscillator = this.audioCtx.createOscillator();
      const gainNode = this.audioCtx.createGain();

      oscillator.type = "sine";
      oscillator.frequency.value = freq;

      gainNode.gain.setValueAtTime(0.3, startTime);
      gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

      oscillator.connect(gainNode);
      gainNode.connect(this.audioCtx.destination);

      oscillator.start(startTime);
      oscillator.stop(startTime + duration);

      startTime += duration + 0.05;
    });
  }

  /**
   * Toggle sound on/off
   */
  toggle() {
    this.enabled = !this.enabled;
    return this.enabled;
  }
}

// Global sound instance
const soundNotifier = new SoundNotifier();

// ═══════════════════════════════════════════════════════════════════
// MODAL HELPER
// ═══════════════════════════════════════════════════════════════════

const Modal = {
  /**
   * Open a modal by ID
   * @param {string} modalId
   */
  open(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    // Focus trap
    const focusable = modal.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
    );
    if (focusable.length) focusable[0].focus();

    // Close on backdrop click
    modal.addEventListener(
      "click",
      (e) => {
        if (e.target === modal) this.close(modalId);
      },
      { once: true },
    );

    // Close on Escape
    const escHandler = (e) => {
      if (e.key === "Escape") {
        this.close(modalId);
        document.removeEventListener("keydown", escHandler);
      }
    };
    document.addEventListener("keydown", escHandler);
  },

  /**
   * Close a modal by ID
   * @param {string} modalId
   */
  close(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove("active");
    document.body.style.overflow = "";
  },
};

// ═══════════════════════════════════════════════════════════════════
// BROWSER NOTIFICATION
// ═══════════════════════════════════════════════════════════════════

const BrowserNotification = {
  /**
   * Request notification permission
   * @returns {Promise<boolean>}
   */
  async requestPermission() {
    if (!("Notification" in window)) return false;

    if (Notification.permission === "granted") return true;
    if (Notification.permission === "denied") return false;

    const result = await Notification.requestPermission();
    return result === "granted";
  },

  /**
   * Show a browser notification
   * @param {string} title
   * @param {string} body
   * @param {object} options
   */
  async show(title, body, options = {}) {
    const granted = await this.requestPermission();
    if (!granted) return null;

    return new Notification(title, {
      body,
      icon: "/assets/img/icon-192.png",
      badge: "/assets/img/icon-72.png",
      vibrate: [200, 100, 200],
      ...options,
    });
  },
};

// ═══════════════════════════════════════════════════════════════════
// CART BADGE UPDATER
// ═══════════════════════════════════════════════════════════════════

function updateCartBadge() {
  const badges = document.querySelectorAll(".cart-badge");
  const count = cart.getTotalItems();

  badges.forEach((badge) => {
    badge.textContent = count;
    badge.style.display = count > 0 ? "flex" : "none";

    if (count > 0) {
      badge.classList.add("badge-pulse");
      setTimeout(() => badge.classList.remove("badge-pulse"), 600);
    }
  });
}

// Listen for cart changes
cart.onChange(() => updateCartBadge());

// ═══════════════════════════════════════════════════════════════════
// TUKU-STYLE SIDEBAR DRAWER CONTROLLER
// ═══════════════════════════════════════════════════════════════════

const TukuDrawer = {
  drawer: null,
  overlay: null,
  toggleBtn: null,
  closeBtn: null,

  init() {
    this.drawer = document.getElementById("tuku-sidebar-drawer");
    this.overlay = document.getElementById("tuku-drawer-overlay");
    this.toggleBtn = document.getElementById("tuku-drawer-toggle");
    this.closeBtn = document.getElementById("tuku-drawer-close");

    if (!this.drawer || !this.overlay) return;

    if (this.toggleBtn) {
      this.toggleBtn.addEventListener("click", () => this.toggle());
    }

    if (this.closeBtn) {
      this.closeBtn.addEventListener("click", () => this.close());
    }

    this.overlay.addEventListener("click", () => this.close());

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isOpen()) {
        this.close();
      }
    });
  },

  isOpen() {
    return this.drawer && this.drawer.classList.contains("active");
  },

  open() {
    if (!this.drawer || !this.overlay) return;
    this.drawer.classList.add("active");
    this.overlay.classList.add("active");
    this.drawer.setAttribute("aria-hidden", "false");
    this.overlay.setAttribute("aria-hidden", "false");
    if (this.toggleBtn) this.toggleBtn.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
  },

  close() {
    if (!this.drawer || !this.overlay) return;
    this.drawer.classList.remove("active");
    this.overlay.classList.remove("active");
    this.drawer.setAttribute("aria-hidden", "true");
    this.overlay.setAttribute("aria-hidden", "true");
    if (this.toggleBtn) this.toggleBtn.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
  },

  toggle() {
    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  },
};

// ═══════════════════════════════════════════════════════════════════
// FLASH MESSAGE & NAVIGATION INITIALIZATION
// ═══════════════════════════════════════════════════════════════════

document.addEventListener("DOMContentLoaded", () => {
  // Initialize Tuku-style Drawer
  TukuDrawer.init();

  // User Dropdown toggle
  const userBtn = document.getElementById("user-menu-btn");
  const userDropdown = document.getElementById("user-dropdown");
  if (userBtn && userDropdown) {
    userBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("show");
    });
    document.addEventListener("click", (e) => {
      if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove("show");
      }
    });
  }

  // Auto-dismiss flash messages
  document
    .querySelectorAll(".flash-message[data-auto-dismiss]")
    .forEach((el) => {
      const delay = parseInt(el.dataset.autoDismiss) || 5000;
      setTimeout(() => {
        el.classList.add("flash-hide");
        setTimeout(() => el.remove(), 500);
      }, delay);
    });

  // Initialize cart badge
  updateCartBadge();

  // Initialize sound on first user interaction
  document.addEventListener(
    "click",
    () => {
      soundNotifier.init();
    },
    { once: true },
  );

  // Mobile menu toggle fallback (if present)
  const menuToggle = document.getElementById("menu-toggle");
  const navMenu = document.getElementById("nav-menu");
  if (menuToggle && navMenu) {
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("nav-open");
      menuToggle.classList.toggle("active");
      const isOpen = navMenu.classList.contains("nav-open");
      menuToggle.setAttribute("aria-expanded", isOpen);
    });
  }
});

// ═══════════════════════════════════════════════════════════════════
// FORM VALIDATION HELPERS
// ═══════════════════════════════════════════════════════════════════

const Validator = {
  /**
   * Check if email format is valid
   * @param {string} email
   * @returns {boolean}
   */
  isEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  },

  /**
   * Check minimum length
   * @param {string} value
   * @param {number} min
   * @returns {boolean}
   */
  minLength(value, min) {
    return value.length >= min;
  },

  /**
   * Check if phone number format is valid (Indonesian)
   * @param {string} phone
   * @returns {boolean}
   */
  isPhone(phone) {
    return /^(\+62|62|0)[0-9]{9,13}$/.test(phone.replace(/[\s-]/g, ""));
  },

  /**
   * Show validation error on a field
   * @param {HTMLElement} input
   * @param {string} message
   */
  showError(input, message) {
    const group = input.closest(".form-group");
    if (!group) return;

    input.classList.add("input-error");

    let errorEl = group.querySelector(".form-error");
    if (!errorEl) {
      errorEl = document.createElement("span");
      errorEl.className = "form-error";
      group.appendChild(errorEl);
    }
    errorEl.textContent = message;
  },

  /**
   * Clear validation error from a field
   * @param {HTMLElement} input
   */
  clearError(input) {
    const group = input.closest(".form-group");
    if (!group) return;

    input.classList.remove("input-error");
    const errorEl = group.querySelector(".form-error");
    if (errorEl) errorEl.remove();
  },

  /**
   * Clear all errors in a form
   * @param {HTMLFormElement} form
   */
  clearAll(form) {
    form
      .querySelectorAll(".input-error")
      .forEach((el) => el.classList.remove("input-error"));
    form.querySelectorAll(".form-error").forEach((el) => el.remove());
  },
};

// ═══════════════════════════════════════════════════════════════════
// LOADING STATE HELPER
// ═══════════════════════════════════════════════════════════════════

const Loading = {
  /**
   * Set button to loading state
   * @param {HTMLButtonElement} btn
   * @param {string} loadingText
   */
  start(btn, loadingText = "Memproses...") {
    btn.disabled = true;
    btn.dataset.originalText = btn.innerHTML;
    btn.classList.add("btn-loading");
    btn.innerHTML = `<span class="spinner"></span> ${loadingText}`;
  },

  /**
   * Restore button from loading state
   * @param {HTMLButtonElement} btn
   */
  stop(btn) {
    btn.disabled = false;
    btn.classList.remove("btn-loading");
    if (btn.dataset.originalText) {
      btn.innerHTML = btn.dataset.originalText;
      delete btn.dataset.originalText;
    }
  },
};
