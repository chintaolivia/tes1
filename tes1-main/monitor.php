<?php
/**
 * Monitor Layar TV Antrian Outlet Salt Bread
 * Menampilkan 4 Kolom: Sedang Dipanggang, Sedang Dikemas, Siap Diambil, dan Rak Mandiri
 */

require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Antrian Outlet - Salt Bread Artisan Bakery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gaegu:wght@400;700&family=Patrick+Hand&family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; overflow-x: hidden; }
    </style>
</head>
<body class="monitor-body">

    <!-- Header Bar -->
    <header class="monitor-header">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; background: #334155; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #fde68a;">SB</div>
            <div>
                <h1 style="font-size: 26px; font-weight: 700; letter-spacing: 1px; color: #f8fafc; margin: 0;">
                    <span style="font-size: 24px; color: #fde68a;">little</span> SALT BREAD • STATUS ANTRIAN LIVE
                </h1>
                <div style="font-size: 13px; color: #94a3b8;">
                    Fresh Oven Bakery • Harap perhatikan nomor antrian pada struk pemesanan Anda
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 20px;">
            <button type="button" class="btn-sound-toggle" id="btnAudioToggle" onclick="enableAudio()" style="background:#1e293b; color:#fbbf24; border-color:#475569;">
                Aktifkan Suara Panggilan
            </button>
            <div style="text-align: right;">
                <div id="liveClock" style="font-size: 26px; font-weight: 900; color: #fbbf24; font-family: monospace;">00:00:00</div>
                <div id="liveDate" style="font-size: 12px; color: #94a3b8;">Senin, 07 September 2026</div>
            </div>
        </div>
    </header>

    <!-- 4-Column Grid -->
    <div class="monitor-grid">
        
        <!-- Kolom 1: Sedang Dipanggang -->
        <div class="monitor-col">
            <div class="monitor-col-head col-head-baking">
                SEDANG DIPANGGANG
            </div>
            <div class="monitor-list" id="colBaking">
                <div style="text-align: center; color: #64748b; padding-top: 40px;">Tidak ada panggangan aktif</div>
            </div>
        </div>

        <!-- Kolom 2: Sedang Dikemas -->
        <div class="monitor-col">
            <div class="monitor-col-head col-head-packing">
                SEDANG DIKEMAS
            </div>
            <div class="monitor-list" id="colPacking">
                <div style="text-align: center; color: #64748b; padding-top: 40px;">Tidak ada kemasan aktif</div>
            </div>
        </div>

        <!-- Kolom 3: Siap Diambil di Counter -->
        <div class="monitor-col" style="border: 2px solid #059669;">
            <div class="monitor-col-head col-head-ready">
                SIAP DIAMBIL (COUNTER)
            </div>
            <div class="monitor-list" id="colReady">
                <div style="text-align: center; color: #64748b; padding-top: 40px;">Belum ada pesanan siap</div>
            </div>
        </div>

        <!-- Kolom 4: Rak Pengambilan Mandiri (>20 Mnt) -->
        <div class="monitor-col" style="border: 2px solid #ef4444;">
            <div class="monitor-col-head col-head-shelf">
                RAK MANDIRI (>20 MNT)
            </div>
            <div class="monitor-list" id="colShelf">
                <div style="text-align: center; color: #64748b; padding-top: 40px;">Rak mandiri kosong</div>
            </div>
        </div>

    </div>

    <!-- Ticker Running Text Bawah -->
    <div style="background: #1e293b; border-top: 1px solid #334155; padding: 8px 24px; font-size: 13px; color: #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <strong>PEMBERITAHUAN:</strong> Roti yang belum diambil lebih dari <strong>20 menit</strong> akan disimpan di <strong>Rak Pengambilan Mandiri Berpenghangat</strong> agar tetap hangat & renyah.
        </div>
        <div style="font-size: 12px; color: #94a3b8;">
            Auto-refresh setiap 3 detik
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>
        let knownReadyQueues = new Set();
        let audioActive = false;

        function enableAudio() {
            soundNotifier.init();
            soundNotifier.playChime();
            audioActive = true;
            const btn = document.getElementById('btnAudioToggle');
            btn.textContent = 'Panggilan Audio Aktif';
            btn.style.background = '#064e3b';
            btn.style.color = '#34d399';
            btn.style.borderColor = '#059669';
        }

        // Live Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('id-ID');
            document.getElementById('liveDate').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Render card
        function createQueueCard(item, type) {
            let extraClass = '';
            let subTag = '';

            if (type === 'ready') {
                extraClass = 'flash-ready';
                subTag = `<div style="font-size:12px; color:#6ee7b7; font-weight:700; margin-top:4px;">Silakan menuju counter</div>`;
            } else if (type === 'shelf') {
                extraClass = 'flash-shelf';
                subTag = `<div class="monitor-shelf-tag">${item.shelf_slot}</div>`;
            }

            return `
                <div class="monitor-card ${extraClass}">
                    <div class="monitor-queue-num">${item.queue_number}</div>
                    <div class="monitor-cust-name">${item.customer_name}</div>
                    ${subTag}
                </div>
            `;
        }

        async function fetchQueueData() {
            try {
                const res = await fetch('api/get_live_queue.php');
                const data = await res.json();

                if (!data.success) return;

                // Render Baking
                const bEl = document.getElementById('colBaking');
                bEl.innerHTML = data.baking.length > 0 
                    ? data.baking.map(it => createQueueCard(it, 'baking')).join('')
                    : `<div style="text-align: center; color: #64748b; padding-top: 40px;">Tidak ada panggangan aktif</div>`;

                // Render Packing
                const pEl = document.getElementById('colPacking');
                pEl.innerHTML = data.packing.length > 0 
                    ? data.packing.map(it => createQueueCard(it, 'packing')).join('')
                    : `<div style="text-align: center; color: #64748b; padding-top: 40px;">Tidak ada kemasan aktif</div>`;

                // Render Ready
                const rEl = document.getElementById('colReady');
                let hasNewReady = false;
                data.ready.forEach(it => {
                    if (!knownReadyQueues.has(it.queue_number)) {
                        knownReadyQueues.add(it.queue_number);
                        hasNewReady = true;
                    }
                });

                if (hasNewReady && audioActive) {
                    soundNotifier.playChime();
                }

                rEl.innerHTML = data.ready.length > 0 
                    ? data.ready.map(it => createQueueCard(it, 'ready')).join('')
                    : `<div style="text-align: center; color: #64748b; padding-top: 40px;">Belum ada pesanan siap</div>`;

                // Render Shelf (> 20 Min)
                const sEl = document.getElementById('colShelf');
                sEl.innerHTML = data.shelf.length > 0 
                    ? data.shelf.map(it => createQueueCard(it, 'shelf')).join('')
                    : `<div style="text-align: center; color: #64748b; padding-top: 40px;">Rak mandiri kosong</div>`;

            } catch (err) {
                console.error('Monitor fetch error:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchQueueData();
            setInterval(fetchQueueData, 3500);
        });
    </script>
</body>
</html>

