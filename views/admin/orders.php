<?php
/**
 * Admin Live Orders POS & Kitchen Monitor — Clean & Compact UI
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
initSession();

requireAdmin('/views/auth/login.php');

$pageTitle = 'Live POS & Dapur Antrean';
$extraCss = 'admin.css';

require_once __DIR__ . '/../partials/header.php';
?>

<style>
/* ─── Compact & Clean Order Cards ────────────────────────────── */
.order-card-compact {
    background: var(--bg-card);
    border-radius: 16px;
    border: 1px solid var(--border);
    border-top: 4px solid var(--primary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.order-card-compact:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}
.order-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 6px;
    position: relative;
}
.order-card-time {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.order-card-queue {
    font-family: var(--font-display);
    font-size: 1.85rem;
    font-weight: 800;
    color: var(--primary);
    line-height: 1;
    margin: 2px 0;
}
.order-card-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 175px;
}
.order-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
}
.badge-paid-status {
    font-size: 0.72rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}
.badge-paid {
    background: rgba(16, 185, 129, 0.12);
    color: #059669;
}
.badge-unpaid {
    background: rgba(245, 158, 11, 0.15);
    color: #d97706;
}
.order-type-icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    flex-shrink: 0;
}
.card-meatball-wrap {
    position: relative;
}
.btn-meatball {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
    padding: 0;
}
.btn-meatball:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.card-meatball-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    min-width: 160px;
    z-index: 50;
    padding: 4px;
    animation: fadeIn 0.15s ease;
}
.card-meatball-dropdown.show {
    display: block;
}
.card-dropdown-item {
    width: 100%;
    border: none;
    background: transparent;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: var(--error);
    transition: background 0.15s ease;
}
.card-dropdown-item:hover {
    background: rgba(220, 38, 38, 0.08);
}
.order-items-compact {
    flex-grow: 1;
    margin: 8px 0 10px 0;
    padding: 8px 0;
    border-top: 1px dashed var(--border);
    border-bottom: 1px dashed var(--border);
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.order-item-line {
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-size: 0.88rem;
    line-height: 1.35;
    color: var(--text-primary);
}
.order-item-qty {
    font-weight: 800;
    color: var(--primary);
    font-size: 0.85rem;
    min-width: 22px;
}
.order-item-name {
    font-weight: 600;
    word-break: break-word;
}
.order-notes-tag {
    margin-top: 4px;
    padding: 4px 8px;
    background: var(--bg-secondary);
    border-radius: 6px;
    font-size: 0.78rem;
    color: var(--text-secondary);
    font-style: italic;
    line-height: 1.3;
}
.order-alert-compact {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-alert-ready {
    background: rgba(5, 150, 105, 0.1);
    color: var(--success);
}
.order-alert-shelf {
    background: #fff7ed;
    color: #c2410c;
    font-weight: 600;
}
.btn-order-action {
    width: 100%;
    height: 38px;
    border-radius: 10px;
    border: none;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: transform 0.1s ease, filter 0.15s ease;
}
.btn-order-action:hover {
    filter: brightness(1.05);
}
.btn-order-action:active {
    transform: scale(0.98);
}
.btn-action-pay {
    background: #10b981;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.btn-action-bake {
    background: #d97706;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.btn-action-ready {
    background: #059669;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.btn-action-shelf {
    background: #ea580c;
    color: #ffffff;
}
.btn-action-done {
    background: #059669;
    color: #ffffff;
}
</style>

<div class="container section" style="padding-top: 20px; padding-bottom: 90px;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin: 0; color: var(--primary-dark);">Live POS & Dapur</h1>
                <span class="badge badge-success badge-pulse" id="live-indicator">LIVE ONLINE</span>
            </div>
            <p style="color: var(--text-secondary); margin: 4px 0 0; font-size: 0.95rem;">Pantau proses pemanggangan, pengemasan, dan serah terima pesanan secara real-time.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-secondary btn-sm" id="btn-toggle-sound">
                Notifikasi Suara: ON
            </button>
            <a href="<?= $baseUrl ?>/monitor.php" target="_blank" class="btn btn-primary btn-sm">
                Buka TV Monitor Display
            </a>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px; scrollbar-width: none;" id="status-filters">
        <button class="btn btn-sm btn-primary filter-tab active" data-status="all">Semua Antrean Aktif (<span id="count-all">0</span>)</button>
        <button class="btn btn-sm btn-secondary filter-tab" data-status="processing">Dipanggang (<span id="count-baking">0</span>)</button>
        <button class="btn btn-sm btn-secondary filter-tab" data-status="ready">Siap di Counter (<span id="count-ready">0</span>)</button>
        <button class="btn btn-sm btn-secondary filter-tab" data-status="shelf">Rak Mandiri (<span id="count-shelf">0</span>)</button>
        <button class="btn btn-sm btn-secondary filter-tab" data-status="completed">Selesai Hari Ini</button>
    </div>

    <!-- Queue Grid (Compact) -->
    <div id="orders-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 14px;">
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
            Memuat data antrean...
        </div>
    </div>
</div>

<script>
let currentFilter = 'all';
let soundEnabled = true;
let previousOrderIds = new Set();
let isFirstLoad = true;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('orders-container');
    const soundBtn = document.getElementById('btn-toggle-sound');

    if (soundBtn) {
        soundBtn.addEventListener('click', () => {
            soundEnabled = soundNotifier.toggle();
            soundBtn.textContent = soundEnabled ? 'Notifikasi Suara: ON' : 'Notifikasi Suara: OFF';
        });
    }

    // Filter tab handler
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.filter-tab').forEach(t => {
                t.classList.remove('btn-primary', 'active');
                t.classList.add('btn-secondary');
            });
            tab.classList.remove('btn-secondary');
            tab.classList.add('btn-primary', 'active');
            currentFilter = tab.dataset.status;
            fetchLiveOrders();
        });
    });

    async function fetchLiveOrders() {
        try {
            const res = await fetchJSON('<?= $baseUrl ?>/api/get_orders.php?type=live');
            if (!res || !res.success) return;

            const orders = res.data?.orders || [];
            
            // Counts
            let cBaking = 0, cReady = 0, cShelf = 0;
            orders.forEach(o => {
                if (o.order_status === 'processing') cBaking++;
                if (o.order_status === 'ready') cReady++;
                if (o.order_status === 'shelf') cShelf++;
            });
            document.getElementById('count-all').textContent = orders.length;
            document.getElementById('count-baking').textContent = cBaking;
            document.getElementById('count-ready').textContent = cReady;
            document.getElementById('count-shelf').textContent = cShelf;

            // Audio Alert check for new orders
            const currentIds = new Set(orders.map(o => o.id));
            if (!isFirstLoad) {
                let hasNew = false;
                currentIds.forEach(id => {
                    if (!previousOrderIds.has(id)) hasNew = true;
                });
                if (hasNew && soundEnabled) {
                    soundNotifier.play('order');
                    Toast.info('Pesanan baru masuk!');
                }
            }
            previousOrderIds = currentIds;
            isFirstLoad = false;

            // Filter
            const filtered = orders.filter(o => {
                if (currentFilter === 'all') return true;
                return o.order_status === currentFilter;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius); border: 1px dashed var(--border);">
                        <div style="width: 48px; height: 48px; margin: 0 auto 12px; color: var(--text-muted);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line></svg>
                        </div>
                        <h3 style="font-family: var(--font-display); margin: 12px 0 4px;">Tidak Ada Antrean</h3>
                        <p style="color: var(--text-muted); margin: 0;">Semua pesanan pada status ini sudah tertangani.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filtered.map(o => {
                const isPaid = o.payment_status === 'paid';
                const items = o.items || [];
                const readySince = o.ready_at ? new Date(o.ready_at) : null;
                const minutesAtCounter = readySince ? Math.floor((Date.now() - readySince.getTime()) / 60000) : 0;
                const isDineIn = o.order_type === 'dine_in';

                // Status border accent
                let borderAccent = 'var(--primary)';
                if (o.order_status === 'ready') borderAccent = 'var(--success)';
                if (o.order_status === 'shelf') borderAccent = '#ea580c';

                return `
                    <div class="order-card-compact" style="border-top-color: ${borderAccent};" id="order-card-${o.id}">
                        <!-- Card Header -->
                        <div class="order-card-header">
                            <div>
                                <span class="order-card-time">${timeAgo(o.created_at)}</span>
                                <div class="order-card-queue">
                                    ${o.queue_number}
                                </div>
                                <div class="order-card-name" title="${escapeHtml(o.customer_name)}">${escapeHtml(o.customer_name)}</div>
                            </div>
                            <div class="order-card-meta">
                                <span class="badge-paid-status ${isPaid ? 'badge-paid' : 'badge-unpaid'}">
                                    ${isPaid ? 'PAID' : 'UNPAID'}
                                </span>
                                <span class="order-type-icon-badge" title="${isDineIn ? 'Dine-In' : 'Take-Away'}">
                                    ${isDineIn ? `
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="2" x2="6" y2="4"></line><line x1="10" y1="2" x2="10" y2="4"></line><line x1="14" y1="2" x2="14" y2="4"></line></svg>
                                    ` : `
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-2z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    `}
                                </span>
                                <div class="card-meatball-wrap">
                                    <button type="button" class="btn-meatball" onclick="toggleCardMenu(event, ${o.id})" title="Opsi Pesanan" aria-label="Opsi">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="2.2"></circle>
                                            <circle cx="12" cy="12" r="2.2"></circle>
                                            <circle cx="12" cy="19" r="2.2"></circle>
                                        </svg>
                                    </button>
                                    <div class="card-meatball-dropdown" id="card-menu-${o.id}">
                                        <button type="button" class="card-dropdown-item" onclick="cancelOrder(${o.id})">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                            <span>Batalkan Pesanan</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 20-min Shelf Countdown Warning if Ready -->
                        ${o.order_status === 'ready' ? `
                            <div class="order-alert-compact order-alert-ready">
                                <span>Di Counter: <strong>${minutesAtCounter} mnt</strong></span>
                                <span style="font-size: 0.72rem; opacity: 0.85;">>20 mnt auto ke rak</span>
                            </div>
                        ` : ''}

                        ${o.order_status === 'shelf' ? `
                            <div class="order-alert-compact order-alert-shelf">
                                Berada di: ${escapeHtml(o.shelf_slot || 'Rak Mandiri A-01')}
                            </div>
                        ` : ''}

                        <!-- Items list (Hanya Qty & Nama Menu, Tanpa Harga!) -->
                        <div class="order-items-compact">
                            ${items.map(it => `
                                <div class="order-item-line">
                                    <span class="order-item-qty">${it.quantity}x</span>
                                    <span class="order-item-name">${escapeHtml(it.item_name)}</span>
                                </div>
                            `).join('')}

                            ${o.notes ? `
                                <div class="order-notes-tag">
                                    "${escapeHtml(o.notes)}"
                                </div>
                            ` : ''}
                        </div>

                        <!-- Action Button (Satu Tombol Utama Sesuai Kondisi) -->
                        <div style="margin-top: auto;">
                            ${renderActionButtons(o)}
                        </div>
                    </div>
                `;
            }).join('');

        } catch (err) {
            console.error('Polling error:', err);
        }
    }

    function renderActionButtons(order) {
        const id = order.id;
        const st = order.order_status;
        const isPaid = order.payment_status === 'paid';

        // 1. Jika status UNPAID -> Satu tombol hijau "Konfirmasi Bayar"
        if (!isPaid) {
            return `
                <button type="button" class="btn-order-action btn-action-pay" onclick="verifyPayment(${id})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Konfirmasi Bayar</span>
                </button>
            `;
        }

        // 2. Jika status PAID -> Satu tombol utama sesuai tahapan dapur
        if (st === 'pending' || st === 'confirmed') {
            return `
                <button type="button" class="btn-order-action btn-action-bake" onclick="changeStatus(${id}, 'processing')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>
                    <span>Masuk Oven / Panggang</span>
                </button>
            `;
        } else if (st === 'processing') {
            return `
                <button type="button" class="btn-order-action btn-action-ready" onclick="changeStatus(${id}, 'ready')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Siap Diambil di Counter</span>
                </button>
            `;
        } else if (st === 'ready') {
            return `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                    <button type="button" class="btn-order-action btn-action-shelf" onclick="changeStatus(${id}, 'shelf')">
                        Pindah Rak
                    </button>
                    <button type="button" class="btn-order-action btn-action-done" onclick="changeStatus(${id}, 'completed')">
                        Selesai
                    </button>
                </div>
            `;
        } else if (st === 'shelf') {
            return `
                <button type="button" class="btn-order-action btn-action-done" onclick="changeStatus(${id}, 'completed')">
                    Sudah Diambil Pelanggan
                </button>
            `;
        }

        return '';
    }

    window.toggleCardMenu = function(e, id) {
        e.stopPropagation();
        const targetMenu = document.getElementById(`card-menu-${id}`);
        const wasOpen = targetMenu && targetMenu.classList.contains('show');
        
        // Close all dropdowns
        document.querySelectorAll('.card-meatball-dropdown.show').forEach(m => m.classList.remove('show'));
        
        if (targetMenu && !wasOpen) {
            targetMenu.classList.add('show');
        }
    };

    window.cancelOrder = async function(orderId) {
        document.querySelectorAll('.card-meatball-dropdown.show').forEach(m => m.classList.remove('show'));
        if (confirm('Batalkan pesanan ini? Stok produk akan dikembalikan ke inventaris.')) {
            changeStatus(orderId, 'cancelled');
        }
    };

    document.addEventListener('click', () => {
        document.querySelectorAll('.card-meatball-dropdown.show').forEach(m => m.classList.remove('show'));
    });

    window.changeStatus = async function(orderId, newStatus) {
        try {
            const res = await postJSON('<?= $baseUrl ?>/api/update_order_status.php', {
                order_id: orderId,
                new_status: newStatus
            });
            if (res.success) {
                Toast.success(`Status pesanan diubah ke "${newStatus}"`);
                fetchLiveOrders();
            } else {
                Toast.error(res.message || 'Gagal mengubah status.');
            }
        } catch (err) {
            Toast.error(err.message || 'Terjadi kesalahan sistem.');
        }
    };

    window.verifyPayment = async function(orderId) {
        try {
            const res = await postJSON('<?= $baseUrl ?>/api/update_order_status.php', {
                order_id: orderId,
                new_status: 'processing',
                payment_status: 'paid'
            });
            Toast.success('Pembayaran kasir diverifikasi!');
            fetchLiveOrders();
        } catch (err) {
            Toast.error('Gagal verifikasi pembayaran: ' + err.message);
        }
    };

    // Auto poll every 3.5 seconds
    setInterval(fetchLiveOrders, 3500);
    fetchLiveOrders();
});
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
