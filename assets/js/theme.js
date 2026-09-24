/**
 * Theme Manager — Dark/Light Mode Toggle
 * Little Salt Bread Blok M — POS System
 *
 * IMPORTANT: This script is loaded in <head> as inline script
 * to prevent FOUC (Flash of Unstyled Content).
 */
(function () {
  "use strict";

  const STORAGE_KEY = "sb_theme";
  const THEMES = { LIGHT: "light", DARK: "dark" };

  /**
   * Get stored theme or detect system preference
   */
  function getPreferredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored && (stored === THEMES.LIGHT || stored === THEMES.DARK)) {
      return stored;
    }
    // Respect system preference
    if (
      window.matchMedia &&
      window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
      return THEMES.DARK;
    }
    return THEMES.LIGHT;
  }

  /**
   * Apply theme to document IMMEDIATELY (before render)
   */
  function applyTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);

    // Update meta theme-color for mobile browsers
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
      metaThemeColor.content = theme === THEMES.DARK ? "#0f172a" : "#d97706";
    }
  }

  /**
   * Save theme preference
   */
  function saveTheme(theme) {
    localStorage.setItem(STORAGE_KEY, theme);
  }

  /**
   * Toggle between light and dark
   */
  function toggleTheme() {
    const current =
      document.documentElement.getAttribute("data-theme") || THEMES.LIGHT;
    const next = current === THEMES.LIGHT ? THEMES.DARK : THEMES.LIGHT;

    applyTheme(next);
    saveTheme(next);
    updateToggleButton(next);

    // Dispatch custom event for other components
    document.dispatchEvent(
      new CustomEvent("themechange", { detail: { theme: next } }),
    );
  }

  /**
   * Update the toggle button icon
   */
  function updateToggleButton(theme) {
    const btn = document.getElementById("theme-toggle");
    if (!btn) return;

    const isDark = theme === THEMES.DARK;
    const labelText = isDark
      ? "Beralih ke mode terang (Matahari)"
      : "Beralih ke mode gelap (Bulan)";

    // Clean SVGs for Sun and Moon (NO EMOJIS)
    const sunIcon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;
    const moonIcon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;

    btn.innerHTML = isDark ? sunIcon : moonIcon;
    btn.setAttribute("aria-label", labelText);
    btn.setAttribute("title", labelText);
  }

  // ─── Apply theme immediately on script load ──────────────────
  const initialTheme = getPreferredTheme();
  applyTheme(initialTheme);

  // ─── Listen for system preference changes ────────────────────
  if (window.matchMedia) {
    window
      .matchMedia("(prefers-color-scheme: dark)")
      .addEventListener("change", (e) => {
        if (!localStorage.getItem(STORAGE_KEY)) {
          const theme = e.matches ? THEMES.DARK : THEMES.LIGHT;
          applyTheme(theme);
          updateToggleButton(theme);
        }
      });
  }

  // ─── Initialize toggle button when DOM is ready ──────────────
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
      updateToggleButton(initialTheme);

      const btn = document.getElementById("theme-toggle");
      if (btn) {
        btn.addEventListener("click", toggleTheme);
      }
    });
  } else {
    updateToggleButton(initialTheme);
    const btn = document.getElementById("theme-toggle");
    if (btn) {
      btn.addEventListener("click", toggleTheme);
    }
  }

  // ─── Expose globally ─────────────────────────────────────────
  window.ThemeManager = {
    toggle: toggleTheme,
    get current() {
      return (
        document.documentElement.getAttribute("data-theme") || THEMES.LIGHT
      );
    },
    set: (theme) => {
      if (theme === THEMES.LIGHT || theme === THEMES.DARK) {
        applyTheme(theme);
        saveTheme(theme);
        updateToggleButton(theme);
      }
    },
  };
})();
