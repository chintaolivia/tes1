<?php
/**
 * Splash Screen Partial
 * Little Salt Bread Blok M — POS System
 * 
 * PWA-friendly splash/loading screen shown on app startup.
 * Auto-hides after page fully loads (handled in footer.php).
 */
?>
<style>
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--bg-primary, #ffffff);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

.splash-screen.splash-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.splash-content {
    text-align: center;
    animation: splashFadeIn 0.6s ease-out;
}

.splash-brand {
    font-size: 2rem;
    margin-bottom: 1.5rem;
}

.splash-brand .brand-light {
    font-family: var(--font-accent, 'Gaegu', cursive);
    font-weight: 400;
    color: var(--text-secondary, #6b7280);
}

.splash-brand .brand-bold {
    font-family: var(--font-display, 'Patrick Hand', cursive);
    font-weight: 700;
    color: var(--primary, #d97706);
    letter-spacing: 3px;
}

.splash-spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto;
    border: 4px solid var(--border, #e5e7eb);
    border-top-color: var(--primary, #d97706);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes splashFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
</style>

