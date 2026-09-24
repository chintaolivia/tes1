/**
 * Salt Bread Online Queue & Order System
 * Client-side JavaScript Engine (Cart, Web Audio Chime, Real-time Poller, 20-min Shelf detector)
 */

// 1. Web Audio API Chime Synthesizer
class SoundNotifier {
    constructor() {
        this.ctx = null;
    }

    init() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.ctx = new AudioContext();
            }
        }
    }

    // Melodic double chime (Ding-Dong)
    playChime() {
        try {
            this.init();
            if (!this.ctx) return;
            if (this.ctx.state === 'suspended') {
                this.ctx.resume();
            }

            const now = this.ctx.currentTime;

            // Note 1 (High bell - E5 / 659.25 Hz)
            const osc1 = this.ctx.createOscillator();
            const gain1 = this.ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0, now);
            gain1.gain.linearRampToValueAtTime(0.4, now + 0.05);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
            osc1.connect(gain1);
            gain1.connect(this.ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.9);

            // Note 2 (Sweet chime - C5 / 523.25 Hz)
            const osc2 = this.ctx.createOscillator();
            const gain2 = this.ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(523.25, now + 0.25);
            gain2.gain.setValueAtTime(0, now + 0.25);
            gain2.gain.linearRampToValueAtTime(0.4, now + 0.3);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
            osc2.connect(gain2);
            gain2.connect(this.ctx.destination);
            osc2.start(now + 0.25);
            osc2.stop(now + 1.2);
        } catch (e) {
            console.log('Audio playback error:', e);
        }
    }

    // Warning sound for 20-min limit
    playWarning() {
        try {
            this.init();
            if (!this.ctx) return;
            const now = this.ctx.currentTime;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(440, now);
            gain.gain.setValueAtTime(0.3, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc.connect(gain);
            gain.connect(this.ctx.destination);
            osc.start(now);
            osc.stop(now + 0.6);
        } catch (e) {}
    }
}

const soundNotifier = new SoundNotifier();

// 2. Shopping Cart Management
class CartManager {
    constructor() {
        this.storageKey = 'saltbread_cart';
        this.items = this.loadCart();
    }

    loadCart() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    }

    saveCart() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
        this.updateUI();
    }

    addItem(id, name, price, maxStock, imageUrl) {
        soundNotifier.init();
        const existing = this.items.find(item => item.id === id);
        if (existing) {
            if (existing.qty < maxStock) {
                existing.qty++;
                this.saveCart();
                this.showToast(`+1 ${name} dimasukkan ke keranjang`);
            } else {
                alert(`Maaf, stok ${name} tersisa ${maxStock} pcs.`);
            }
        } else {
            if (maxStock > 0) {
                this.items.push({
                    id: id,
                    name: name,
                    price: price,
                    qty: 1,
                    maxStock: maxStock,
                    imageUrl: imageUrl
                });
                this.saveCart();
                this.showToast(`${name} berhasil ditambah ke pesanan!`);
            } else {
                alert(`Maaf, ${name} sedang habis.`);
            }
        }
    }

    updateQty(id, delta) {
        const item = this.items.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty <= 0) {
            this.items = this.items.filter(i => i.id !== id);
        } else if (item.qty > item.maxStock) {
            item.qty = item.maxStock;
            alert(`Maksimal stok tersedia hanya ${item.maxStock} pcs.`);
        }
        this.saveCart();
    }

    clearCart() {
        this.items = [];
        this.saveCart();
    }

    getTotalQty() {
        return this.items.reduce((sum, item) => sum + item.qty, 0);
    }

    getTotalPrice() {
        return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    updateUI() {
        const totalQty = this.getTotalQty();
        const totalPrice = this.getTotalPrice();

        // Update Floating Cart Bar
        const floatBar = document.getElementById('cartFloatingBar');
        const floatQty = document.getElementById('cartFloatQty');
        const floatPrice = document.getElementById('cartFloatPrice');
        const navBadge = document.getElementById('navCartCount');

        if (navBadge) {
            navBadge.textContent = totalQty;
            navBadge.style.display = totalQty > 0 ? 'inline-block' : 'none';
        }

        if (floatBar) {
            if (totalQty > 0) {
                floatBar.style.display = 'flex';
                if (floatQty) floatQty.textContent = `${totalQty} item dipilih`;
                if (floatPrice) floatPrice.textContent = this.formatRupiah(totalPrice);
            } else {
                floatBar.style.display = 'none';
            }
        }

        // Render in Modal if open
        const modalList = document.getElementById('cartModalItems');
        const modalTotal = document.getElementById('cartModalTotal');
        if (modalList && modalTotal) {
            if (this.items.length === 0) {
                modalList.innerHTML = `
                    <div style="text-align: center; padding: 32px 10px; color: #94a3b8;">
                        <div style="font-size: 40px; margin-bottom: 8px;">🥐</div>
                        <p>Keranjang pesananmu masih kosong.</p>
                    </div>`;
                modalTotal.textContent = 'Rp 0';
            } else {
                modalList.innerHTML = this.items.map(item => `
                    <div class="cart-item-row">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-unit-price">${this.formatRupiah(item.price)} &times; ${item.qty}</div>
                        </div>
                        <div class="cart-qty-ctrl">
                            <button type="button" class="btn-qty" onclick="cart.updateQty(${item.id}, -1)">&minus;</button>
                            <span class="cart-item-qty">${item.qty}</span>
                            <button type="button" class="btn-qty" onclick="cart.updateQty(${item.id}, 1)">&plus;</button>
                        </div>
                        <div style="font-weight: 700; min-width: 80px; text-align: right; color: var(--primary);">
                            ${this.formatRupiah(item.price * item.qty)}
                        </div>
                    </div>
                `).join('');
                modalTotal.textContent = this.formatRupiah(totalPrice);
            }
        }

        // Pass to hidden input on checkout form
        const checkoutInput = document.getElementById('cartDataInput');
        if (checkoutInput) {
            checkoutInput.value = JSON.stringify(this.items);
        }
    }

    formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    showToast(message) {
        let toast = document.getElementById('globalToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'globalToast';
            toast.style.cssText = `
                position: fixed;
                bottom: 80px;
                left: 50%;
                transform: translateX(-50%);
                background: #1e293b;
                color: #ffffff;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 600;
                z-index: 2000;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                transition: opacity 0.3s;
                opacity: 0;
                pointer-events: none;
            `;
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.opacity = '0';
        }, 2200);
    }
}

const cart = new CartManager();

// Modal Open/Close Helpers
function openCartModal() {
    soundNotifier.init();
    cart.updateUI();
    const modal = document.getElementById('cartModal');
    if (modal) modal.classList.add('active');
}

function closeCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) modal.classList.remove('active');
}

// Request Desktop Notifications
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission !== 'granted') {
        Notification.requestPermission();
    }
}

// Trigger Web Notification
function notifyUser(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        try {
            new Notification(title, {
                body: body,
                icon: 'assets/images/classic.svg'
            });
        } catch (e) {}
    }
}

// Global user click to unlock AudioContext
window.addEventListener('click', () => {
    soundNotifier.init();
}, { once: true });

document.addEventListener('DOMContentLoaded', () => {
    cart.updateUI();
});

