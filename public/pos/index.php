<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Item.php';
require_once __DIR__ . '/../../models/Customer.php';
require_once __DIR__ . '/../../models/SaleTransaction.php';

requireLogin();

$pageTitle = 'Sistem Kasir (POS)';
$itemModel = new Item();
$customerModel = new Customer();

$items = $itemModel->getItemsWithCategory();
$customers = $customerModel->getActiveCustomers();

include __DIR__ . '/../../views/layouts/header.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <h2><i class="bi bi-calculator"></i> Sistem Kasir (POS)</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-search"></i> Pilih Barang
            </div>
            <div class="card-body">
                <div class="mb-3 search-box">
                    <input type="text" id="searchItem" class="form-control form-control-lg" 
                           placeholder="Cari barang berdasarkan nama, SKU, atau barcode..." autofocus>
                    <div id="searchResults" class="search-results" style="display: none;"></div>
                </div>
                
                <div class="pos-item-list">
                    <div class="row g-2" id="itemGrid">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-4 col-sm-6 item-card" 
                                 data-id="<?php echo $item['item_id']; ?>"
                                 data-name="<?php echo htmlspecialchars($item['item_name']); ?>"
                                 data-sku="<?php echo htmlspecialchars($item['sku']); ?>"
                                 data-barcode="<?php echo htmlspecialchars($item['barcode'] ?? ''); ?>"
                                 data-price="<?php echo $item['selling_price']; ?>"
                                 data-stock="<?php echo $item['stock_quantity']; ?>"
                                 data-unit="<?php echo $item['unit']; ?>">
                                <div class="card h-100 item-select-card" style="cursor: pointer;">
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1" style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                        </h6>
                                        <p class="card-text mb-1">
                                            <small class="text-muted"><?php echo $item['sku']; ?></small><br>
                                            <strong class="text-primary"><?php echo formatRupiah($item['selling_price']); ?></strong><br>
                                            <small class="<?php echo $item['stock_quantity'] <= $item['min_stock'] ? 'text-danger' : 'text-success'; ?>">
                                                Stok: <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-cart3"></i> Keranjang Belanja
            </div>
            <div class="card-body p-0">
                <div class="p-3">
                    <label class="form-label">Pelanggan (Opsional)</label>
                    <select class="form-select" id="customerId">
                        <option value="">Pelanggan Umum</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>">
                                <?php echo htmlspecialchars($customer['customer_name']); ?>
                                <?php if ($customer['phone'] && $customer['phone'] != '-'): ?>
                                    - <?php echo $customer['phone']; ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="cartItems" class="border-top" style="min-height: 200px; max-height: 300px; overflow-y: auto;">
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                        <p class="mt-2">Keranjang kosong</p>
                    </div>
                </div>
                
                <div class="border-top p-3 bg-light">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Subtotal:</strong></div>
                        <div class="col-6 text-end" id="subtotalDisplay">Rp 0</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label mb-0">Diskon (%):</label>
                        </div>
                        <div class="col-6">
                            <input type="number" id="discountPercent" class="form-control form-control-sm text-end" 
                                   value="0" min="0" max="100" step="0.1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Potongan:</strong></div>
                        <div class="col-6 text-end text-danger" id="discountDisplay">Rp 0</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><h5>TOTAL:</h5></div>
                        <div class="col-6 text-end">
                            <h5 class="text-success mb-0" id="totalDisplay">Rp 0</h5>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <label class="form-label">Uang Bayar:</label>
                            <input type="number" id="paymentAmount" class="form-control form-control-lg text-end" 
                                   placeholder="0" min="0" step="1000">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Kembalian:</strong></div>
                        <div class="col-6 text-end">
                            <h5 class="text-info mb-0" id="changeDisplay">Rp 0</h5>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12">
                            <label class="form-label">Metode Pembayaran:</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Cash / Tunai</option>
                                <option value="debit">Debit Card</option>
                                <option value="credit">Credit Card</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer p-2">
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" id="btnCheckout">
                        <i class="bi bi-check-circle"></i> PROSES PEMBAYARAN
                    </button>
                    <button class="btn btn-danger btn-sm" id="btnClear">
                        <i class="bi bi-x-circle"></i> Bersihkan Keranjang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraJS = [];
include __DIR__ . '/../../views/layouts/footer.php';
?>

<script>
let cart = [];

const searchItem = document.getElementById('searchItem');
const searchResults = document.getElementById('searchResults');
const itemGrid = document.getElementById('itemGrid');
const cartItems = document.getElementById('cartItems');
const discountPercent = document.getElementById('discountPercent');
const paymentAmount = document.getElementById('paymentAmount');
const btnCheckout = document.getElementById('btnCheckout');
const btnClear = document.getElementById('btnClear');

const allItems = Array.from(document.querySelectorAll('.item-card')).map(el => ({
    id: el.dataset.id,
    name: el.dataset.name,
    sku: el.dataset.sku,
    barcode: el.dataset.barcode,
    price: parseFloat(el.dataset.price),
    stock: parseInt(el.dataset.stock),
    unit: el.dataset.unit,
    element: el
}));

searchItem.addEventListener('input', debounce(function() {
    const keyword = this.value.toLowerCase();
    
    if (keyword.length < 2) {
        searchResults.style.display = 'none';
        itemGrid.style.display = 'block';
        allItems.forEach(item => item.element.style.display = 'block');
        return;
    }
    
    const results = allItems.filter(item => 
        item.name.toLowerCase().includes(keyword) ||
        item.sku.toLowerCase().includes(keyword) ||
        item.barcode.toLowerCase().includes(keyword)
    );
    
    if (results.length > 0) {
        searchResults.innerHTML = results.map(item => `
            <div class="search-result-item" data-id="${item.id}">
                <strong>${item.name}</strong><br>
                <small class="text-muted">${item.sku}</small> - 
                <span class="text-primary">${formatRupiah(item.price)}</span> - 
                <small class="${item.stock <= 10 ? 'text-danger' : 'text-success'}">Stok: ${item.stock} ${item.unit}</small>
            </div>
        `).join('');
        searchResults.style.display = 'block';
        itemGrid.style.display = 'none';
    } else {
        searchResults.innerHTML = '<div class="p-3 text-center text-muted">Tidak ada hasil</div>';
        searchResults.style.display = 'block';
        itemGrid.style.display = 'none';
    }
}, 300));

searchResults.addEventListener('click', function(e) {
    const item = e.target.closest('.search-result-item');
    if (item) {
        addToCart(item.dataset.id);
        searchItem.value = '';
        searchResults.style.display = 'none';
        itemGrid.style.display = 'block';
    }
});

document.addEventListener('click', function(e) {
    if (!searchItem.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
        itemGrid.style.display = 'block';
    }
});

itemGrid.addEventListener('click', function(e) {
    const card = e.target.closest('.item-card');
    if (card) {
        addToCart(card.dataset.id);
    }
});

function addToCart(itemId) {
    const item = allItems.find(i => i.id == itemId);
    if (!item) return;
    
    const existing = cart.find(c => c.id == itemId);
    
    if (existing) {
        if (existing.quantity >= item.stock) {
            alert(`Stok ${item.name} hanya tersisa ${item.stock} ${item.unit}`);
            return;
        }
        existing.quantity++;
    } else {
        if (item.stock < 1) {
            alert(`${item.name} sudah habis!`);
            return;
        }
        cart.push({
            id: item.id,
            name: item.name,
            sku: item.sku,
            price: item.price,
            quantity: 1,
            unit: item.unit,
            maxStock: item.stock
        });
    }
    
    renderCart();
    updateTotals();
}

function removeFromCart(itemId) {
    cart = cart.filter(c => c.id != itemId);
    renderCart();
    updateTotals();
}

function updateQuantity(itemId, quantity) {
    const item = cart.find(c => c.id == itemId);
    if (!item) return;
    
    if (quantity > item.maxStock) {
        alert(`Stok ${item.name} hanya tersisa ${item.maxStock} ${item.unit}`);
        quantity = item.maxStock;
    }
    
    if (quantity < 1) {
        removeFromCart(itemId);
        return;
    }
    
    item.quantity = quantity;
    renderCart();
    updateTotals();
}

function renderCart() {
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="p-4 text-center text-muted">
                <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                <p class="mt-2">Keranjang kosong</p>
            </div>
        `;
        return;
    }
    
    cartItems.innerHTML = cart.map(item => `
        <div class="pos-cart-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1">
                    <strong>${item.name}</strong><br>
                    <small class="text-muted">${formatRupiah(item.price)} x ${item.quantity} ${item.unit}</small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="removeFromCart('${item.id}')">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="updateQuantity('${item.id}', ${item.quantity - 1})">-</button>
                    <input type="number" class="form-control text-center" style="width: 60px;" value="${item.quantity}" 
                           min="1" max="${item.maxStock}" onchange="updateQuantity('${item.id}', parseInt(this.value))">
                    <button class="btn btn-outline-secondary" onclick="updateQuantity('${item.id}', ${item.quantity + 1})">+</button>
                </div>
                <strong class="text-primary">${formatRupiah(item.price * item.quantity)}</strong>
            </div>
        </div>
    `).join('');
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discountPct = parseFloat(discountPercent.value) || 0;
    const discountAmount = subtotal * (discountPct / 100);
    const total = subtotal - discountAmount;
    const payment = parseFloat(paymentAmount.value) || 0;
    const change = payment - total;
    
    document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
    document.getElementById('discountDisplay').textContent = formatRupiah(discountAmount);
    document.getElementById('totalDisplay').textContent = formatRupiah(total);
    document.getElementById('changeDisplay').textContent = formatRupiah(Math.max(0, change));
}

discountPercent.addEventListener('input', updateTotals);
paymentAmount.addEventListener('input', updateTotals);

btnClear.addEventListener('click', function() {
    if (confirm('Yakin ingin menghapus semua item di keranjang?')) {
        cart = [];
        renderCart();
        updateTotals();
        paymentAmount.value = '';
        discountPercent.value = 0;
    }
});

btnCheckout.addEventListener('click', async function() {
    if (cart.length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }
    
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discountPct = parseFloat(discountPercent.value) || 0;
    const discountAmount = subtotal * (discountPct / 100);
    const total = subtotal - discountAmount;
    const payment = parseFloat(paymentAmount.value) || 0;
    
    if (payment < total) {
        alert('Uang pembayaran kurang!');
        paymentAmount.focus();
        return;
    }
    
    if (!confirm(`Total: ${formatRupiah(total)}\nBayar: ${formatRupiah(payment)}\nKembalian: ${formatRupiah(payment - total)}\n\nProses transaksi?`)) {
        return;
    }
    
    btnCheckout.disabled = true;
    btnCheckout.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
    
    try {
        const response = await fetch('<?php echo url('pos/process.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                customer_id: document.getElementById('customerId').value || null,
                items: cart.map(item => ({
                    item_id: item.id,
                    quantity: item.quantity,
                    selling_price: item.price
                })),
                subtotal: subtotal,
                discount_percent: discountPct,
                discount_amount: discountAmount,
                total_amount: total,
                payment_amount: payment,
                change_amount: payment - total,
                payment_method: document.getElementById('paymentMethod').value
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Transaksi berhasil!\nNo: ' + result.sale_number);
            window.open('<?php echo url('pos/receipt.php?id='); ?>' + result.sale_id, '_blank');
            
            cart = [];
            renderCart();
            updateTotals();
            paymentAmount.value = '';
            discountPercent.value = 0;
            document.getElementById('customerId').value = '';
            
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan: ' + error.message);
    } finally {
        btnCheckout.disabled = false;
        btnCheckout.innerHTML = '<i class="bi bi-check-circle"></i> PROSES PEMBAYARAN';
    }
});
</script>
