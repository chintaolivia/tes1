<?php
/**
 * Admin Menu & Stock Management
 * Little Salt Bread Blok M — POS System
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/MenuController.php';
initSession();

requireAdmin('/views/auth/login.php');

$categories = MenuController::getCategories()['data'] ?? [];
$products = MenuController::getAll()['data'] ?? [];

$pageTitle = 'Kelola Menu & Stok';
$extraCss = 'admin.css';

require_once __DIR__ . '/../partials/header.php';
?>

<div class="container section" style="padding-top: 20px; padding-bottom: 90px;">
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin: 0 0 4px; color: var(--primary-dark);">Manajemen Menu & Stok</h1>
            <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem;">Kelola ketersediaan bahan, varian Salt Bread, harga, dan foto produk.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddModal()">
            + Tambah Menu Baru
        </button>
    </div>

    <!-- Product Table Card -->
    <div class="card" style="padding: 24px;">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--text-muted);">
                        <th style="padding: 12px 10px;">Produk</th>
                        <th style="padding: 12px 10px;">Kategori</th>
                        <th style="padding: 12px 10px;">Harga</th>
                        <th style="padding: 12px 10px;">Status Ketersediaan</th>
                        <th style="padding: 12px 10px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): 
                        $isAvail = (!isset($p['is_available']) || (int)$p['is_available'] === 1) && (int)$p['stock'] > 0;
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);" id="row-prod-<?= $p['id'] ?>">
                            <td style="padding: 12px 10px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="<?= $baseUrl ?>/<?= htmlspecialchars(ltrim($p['image_url'] ?? 'assets/img/salt_bread_plain.png', '/')) ?>" 
                                         alt="<?= htmlspecialchars($p['name']) ?>" 
                                         id="prod-img-<?= $p['id'] ?>"
                                         style="width: 50px; height: 50px; object-fit: contain; border-radius: 0; background: transparent; transition: all 0.3s ease; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.12)); <?= !$isAvail ? 'filter: grayscale(100%); opacity: 0.55;' : '' ?>"
                                         onerror="this.src='<?= $baseUrl ?>/assets/img/salt_bread_plain.png'">
                                    <div>
                                        <div style="font-weight: 700; font-size: 1rem;"><?= htmlspecialchars($p['name']) ?></div>
                                        <small style="color: var(--text-muted);"><?= $p['bake_time_mins'] ?> mnt oven</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 12px 10px;">
                                <span class="badge badge-secondary"><?= htmlspecialchars($p['category_name'] ?? 'Roti') ?></span>
                            </td>
                            <td style="padding: 12px 10px; font-weight: 700; color: var(--primary);">
                                <?= formatRupiah($p['price']) ?>
                            </td>
                            <td style="padding: 12px 10px;">
                                <!-- 1-Click Status Switch: Tersedia vs Tidak Tersedia -->
                                <button type="button" 
                                        id="status-btn-<?= $p['id'] ?>"
                                        onclick="toggleAvailability(<?= $p['id'] ?>)" 
                                        class="btn btn-sm"
                                        title="Klik untuk beralih antara Tersedia dan Tidak Tersedia"
                                        style="border-radius: var(--radius-pill); font-weight: 700; padding: 6px 16px; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: all 0.2s ease; <?= $isAvail ? 'background: #059669; color: #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);' : 'background: #dc2626; color: #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);' ?>">
                                    <span id="status-dot-<?= $p['id'] ?>" style="width: 8px; height: 8px; border-radius: 50%; background: <?= $isAvail ? '#a7f3d0' : '#fecaca' ?>; display: inline-block;"></span>
                                    <span id="status-text-<?= $p['id'] ?>"><?= $isAvail ? 'Tersedia' : 'Tidak Tersedia' ?></span>
                                </button>
                            </td>
                            <td style="padding: 12px 10px; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick='openEditModal(<?= json_encode($p) ?>)' style="display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="deleteProduct(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')" style="color: var(--error);" title="Hapus menu">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Product -->
<div class="modal" id="prod-modal">
    <div class="modal-content" style="max-width: 540px;">
        <div class="modal-header">
            <h3 id="modal-form-title" style="font-family: var(--font-display); font-size: 1.4rem; margin: 0;">Tambah Menu Baru</h3>
            <button class="modal-close" onclick="Modal.close('prod-modal')">&times;</button>
        </div>
        <form id="prod-form" novalidate>
            <input type="hidden" id="prod-id" name="id" value="">
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" for="prod-name">Nama Menu</label>
                    <input type="text" id="prod-name" name="name" class="form-input" placeholder="Contoh: Salt Bread Kaya" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="prod-category">Kategori</label>
                        <select id="prod-category" name="category_id" class="form-select" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prod-bake-time">Waktu Oven (Menit)</label>
                        <input type="number" id="prod-bake-time" name="bake_time_mins" class="form-input" value="12" min="1">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="prod-price">Harga Satuan (Rp)</label>
                        <input type="number" id="prod-price" name="price" class="form-input" placeholder="28000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prod-status">Status Ketersediaan</label>
                        <select id="prod-status" name="is_available" class="form-select">
                            <option value="1">Tersedia (Ready)</option>
                            <option value="0">Tidak Tersedia (Habis)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" for="prod-desc">Deskripsi Produk</label>
                    <textarea id="prod-desc" name="description" class="form-textarea" rows="2" placeholder="Jelaskan aroma, rasa gurih, atau keunikan menu..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" for="prod-image-url">URL Foto / Jalur Gambar</label>
                    <input type="text" id="prod-image-url" name="image_url" class="form-input" placeholder="assets/img/salt_bread_plain.png">
                    <small style="color: var(--text-muted); font-size: 0.8rem;">Gunakan path lokal atau link gambar produk.</small>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="Modal.close('prod-modal')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btn-save-prod">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal & Action handlers defined on window so inline onclick handlers always work
window.openAddModal = function() {
    const form = document.getElementById('prod-form');
    if (form) form.reset();
    document.getElementById('prod-id').value = '';
    document.getElementById('prod-status').value = '1';
    document.getElementById('modal-form-title').textContent = 'Tambah Menu Baru';
    Modal.open('prod-modal');
};

window.openEditModal = function(product) {
    const form = document.getElementById('prod-form');
    if (form) form.reset();
    document.getElementById('prod-id').value = product.id;
    document.getElementById('prod-name').value = product.name;
    document.getElementById('prod-category').value = product.category_id;
    document.getElementById('prod-price').value = product.price;
    const isAvail = (product.is_available === undefined || product.is_available === null || parseInt(product.is_available) === 1) && parseInt(product.stock) > 0;
    document.getElementById('prod-status').value = isAvail ? '1' : '0';
    document.getElementById('prod-bake-time').value = product.bake_time_mins || 12;
    document.getElementById('prod-desc').value = product.description || '';
    document.getElementById('prod-image-url').value = product.image_url || '';
    document.getElementById('modal-form-title').textContent = 'Edit Menu ' + product.name;
    Modal.open('prod-modal');
};

// 1-Click Toggle Availability: Tersedia vs Tidak Tersedia
window.toggleAvailability = async function(id) {
    const btn = document.getElementById(`status-btn-${id}`);
    const text = document.getElementById(`status-text-${id}`);
    const dot = document.getElementById(`status-dot-${id}`);
    const img = document.getElementById(`prod-img-${id}`);

    try {
        const res = await postJSON('<?= $baseUrl ?>/api/update_product.php', {
            action: 'toggle_availability',
            product_id: id
        });

        if (res.success) {
            const isAvail = res.data && res.data.is_available === 1;
            if (isAvail) {
                btn.style.background = '#059669';
                btn.style.boxShadow = '0 2px 8px rgba(5, 150, 105, 0.3)';
                text.textContent = 'Tersedia';
                dot.style.background = '#a7f3d0';
                if (img) {
                    img.style.filter = 'none';
                    img.style.opacity = '1';
                }
            } else {
                btn.style.background = '#dc2626';
                btn.style.boxShadow = '0 2px 8px rgba(220, 38, 38, 0.3)';
                text.textContent = 'Tidak Tersedia';
                dot.style.background = '#fecaca';
                if (img) {
                    img.style.filter = 'grayscale(100%)';
                    img.style.opacity = '0.55';
                }
            }
            Toast.success(res.message);
        } else {
            Toast.error(res.message || 'Gagal mengubah status');
        }
    } catch (err) {
        Toast.error('Gagal mengubah status: ' + err.message);
    }
};

window.deleteProduct = async function(id, name) {
    if (!confirm(`Hapus menu "${name}" dari katalog?`)) return;
    try {
        const res = await postJSON('<?= $baseUrl ?>/api/update_product.php', {
            action: 'delete_product',
            product_id: id
        });
        document.getElementById(`row-prod-${id}`)?.remove();
        Toast.success(`Menu "${name}" dinonaktifkan`);
    } catch (err) {
        Toast.error('Gagal hapus menu: ' + err.message);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('prod-form');
    const btnSave = document.getElementById('btn-save-prod');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('prod-id').value;
            const name = document.getElementById('prod-name').value.trim();
            const category_id = document.getElementById('prod-category').value;
            const price = parseInt(document.getElementById('prod-price').value) || 0;
            const is_available = parseInt(document.getElementById('prod-status').value) || 0;
            const bake_time_mins = parseInt(document.getElementById('prod-bake-time').value) || 12;
            const description = document.getElementById('prod-desc').value.trim();
            let image_url = document.getElementById('prod-image-url').value.trim();
            if (!image_url) {
                image_url = 'assets/img/salt_bread_plain.png';
            }

            if (!name || price <= 0) {
                Toast.error('Nama menu dan harga valid wajib diisi.');
                return;
            }

            if (typeof Loading !== 'undefined') {
                Loading.start(btnSave, 'Menyimpan...');
            }

            try {
                const res = await postJSON('<?= $baseUrl ?>/api/update_product.php', {
                    action: id ? 'update_product' : 'create_product',
                    id: id || undefined,
                    name, 
                    category_id, 
                    price, 
                    is_available, 
                    bake_time_mins, 
                    description, 
                    image_url
                });

                Toast.success(id ? 'Menu berhasil diperbarui!' : 'Menu berhasil ditambahkan!');
                Modal.close('prod-modal');
                setTimeout(() => window.location.reload(), 600);
            } catch (err) {
                Toast.error('Gagal menyimpan menu: ' + err.message);
                if (typeof Loading !== 'undefined') {
                    Loading.stop(btnSave);
                }
            }
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/../partials/bottom_nav.php';
require_once __DIR__ . '/../partials/footer.php'; 
?>
