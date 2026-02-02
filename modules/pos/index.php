<?php
/**
 * POS (Point of Sale) Module - FIXED VERSION
 * Scan items, add to cart, checkout
 */
require_once '../../init.php';
requirePermission('pos');

$pageTitle = 'Point of Sale';

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-cart3"></i> Point of Sale</h2>
        <hr>
    </div>
</div>

<div class="row">
    <!-- Left Side: Cart -->
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Shopping Cart</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive cart-table">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Price</th>
                                <th>Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartItems">
                            <tr>
                                <td colspan="5" class="text-center py-4">Cart is empty</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <button onclick="clearCart()" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Clear Cart
                    </button>
                    <div class="text-end">
                        <h3 class="mb-0">Total: <span class="cart-total" id="cartTotal">₱0.00</span></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Side: Scanner & Actions -->
    <div class="col-lg-4">
        <div class="card shadow mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-upc-scan"></i> Scan Item</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="barcodeInput" class="form-label">Barcode</label>
                    <input type="text" 
                           class="form-control form-control-lg scanner-input" 
                           id="barcodeInput" 
                           placeholder="Scan or enter barcode"
                           autocomplete="off">
                </div>
                <button onclick="checkout()" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-check-circle"></i> Checkout
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Instructions</h6>
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3">
                    <li class="mb-2">Scan item barcode to add to cart</li>
                    <li class="mb-2">Adjust quantities as needed</li>
                    <li class="mb-2">Click Checkout when ready</li>
                    <li>Enter payment and complete sale</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-stack"></i> Checkout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="checkoutForm">
                <div class="modal-body">
                    <!-- Customer Information -->
                    <h6 class="mb-3"><i class="bi bi-person"></i> Customer Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customerContact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="customerContact" placeholder="e.g., 0917-123-4567">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customerAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="customerAddress" rows="2" placeholder="Enter customer address"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date & Time</label>
                            <input type="text" class="form-control" id="saleDateTime" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="gcash">GCash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount</label>
                            <h3 class="text-primary" id="modalTotal">₱0.00</h3>
                            <input type="hidden" id="checkoutTotal">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amountReceived" class="form-label">Amount Received</label>
                            <input type="number" class="form-control form-control-lg" id="amountReceived" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5>Change: <span id="changeAmount">₱0.00</span></h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-lg" id="submitCheckout">
                        <i class="bi bi-check-circle"></i> Complete Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];
let checkoutModal;

document.addEventListener('DOMContentLoaded', function() {
    console.log('POS System Initialized');
    document.getElementById('barcodeInput').focus();
    loadCart();
    
    // Handle barcode scan
    document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (barcode) {
                addItemToCart(barcode);
            }
        }
    });
    
    // Initialize modal instance
    checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    
    // Calculate change
    document.getElementById('amountReceived').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('checkoutTotal').value) || 0;
        const received = parseFloat(this.value) || 0;
        const change = Math.max(0, received - total);
        document.getElementById('changeAmount').textContent = formatCurrency(Math.max(0, change));
    });
    
    // Process checkout
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        processCheckout();
    });
});

function addItemToCart(barcode) {
    console.log('Adding item with barcode:', barcode);
    
    fetch('pos_api.php?action=getItem', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({barcode: barcode})
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Item data received:', data);
        if (data.success) {
            const item = data.item;
            const existingIndex = cart.findIndex(i => i.item_id === item.item_id);
            
            if (existingIndex >= 0) {
                if (cart[existingIndex].quantity < item.stock_quantity) {
                    cart[existingIndex].quantity++;
                    showToast('Quantity updated', 'success');
                } else {
                    showToast('Insufficient stock!', 'warning');
                }
            } else {
                if (item.stock_quantity > 0) {
                    cart.push({
                        item_id: item.item_id,
                        barcode: item.barcode,
                        item_name: item.item_name,
                        price: parseFloat(item.selling_price),
                        quantity: 1,
                        max_stock: item.stock_quantity
                    });
                    showToast('Item added to cart', 'success');
                } else {
                    showToast('Item out of stock!', 'error');
                }
            }
            
            updateCart();
            playBeep();
        } else {
            showToast(data.message || 'Item not found!', 'error');
        }
        
        document.getElementById('barcodeInput').value = '';
        document.getElementById('barcodeInput').focus();
    })
    .catch(error => {
        console.error('Error adding item:', error);
        showToast('Error adding item: ' + error.message, 'error');
        document.getElementById('barcodeInput').value = '';
        document.getElementById('barcodeInput').focus();
    });
}

function updateCart() {
    saveCart();
    
    const tbody = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    
    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Cart is empty</td></tr>';
        totalEl.textContent = formatCurrency(0);
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        
        html += '<tr>' +
            '<td><strong>' + escapeHtml(item.item_name) + '</strong><br><small class="text-muted">' + escapeHtml(item.barcode) + '</small></td>' +
            '<td class="text-end">' + formatCurrency(item.price) + '</td>' +
            '<td>' +
                '<div class="input-group input-group-sm" style="width: 120px;">' +
                    '<button class="btn btn-outline-secondary" onclick="decrementQty(' + index + ')">-</button>' +
                    '<input type="number" class="form-control text-center" value="' + item.quantity + '" onchange="updateQty(' + index + ', this.value)" min="1" max="' + item.max_stock + '">' +
                    '<button class="btn btn-outline-secondary" onclick="incrementQty(' + index + ')">+</button>' +
                '</div>' +
            '</td>' +
            '<td class="text-end"><strong>' + formatCurrency(subtotal) + '</strong></td>' +
            '<td class="text-center">' +
                '<button class="btn btn-sm btn-danger" onclick="removeItem(' + index + ')"><i class="bi bi-trash"></i></button>' +
            '</td>' +
        '</tr>';
    });
    
    tbody.innerHTML = html;
    totalEl.textContent = formatCurrency(total);
}

function incrementQty(index) {
    if (cart[index].quantity < cart[index].max_stock) {
        cart[index].quantity++;
        updateCart();
    } else {
        showToast('Insufficient stock!', 'warning');
    }
}

function decrementQty(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        updateCart();
    }
}

function updateQty(index, qty) {
    qty = parseInt(qty);
    if (qty >= 1 && qty <= cart[index].max_stock) {
        cart[index].quantity = qty;
        updateCart();
    } else {
        showToast('Invalid quantity!', 'warning');
        updateCart();
    }
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}

function clearCart() {
    if (confirm('Clear all items from cart?')) {
        cart = [];
        updateCart();
        showToast('Cart cleared', 'info');
    }
}

function saveCart() {
    try {
        sessionStorage.setItem('pos_cart', JSON.stringify(cart));
    } catch (e) {
        console.error('Error saving cart:', e);
    }
}

function loadCart() {
    try {
        const saved = sessionStorage.getItem('pos_cart');
        if (saved) {
            cart = JSON.parse(saved);
            updateCart();
        }
    } catch (e) {
        console.error('Error loading cart:', e);
        cart = [];
    }
}

function checkout() {
    if (cart.length === 0) {
        showToast('Cart is empty!', 'warning');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    document.getElementById('modalTotal').textContent = formatCurrency(total);
    document.getElementById('checkoutTotal').value = total.toFixed(2);
    document.getElementById('amountReceived').value = '';
    document.getElementById('changeAmount').textContent = formatCurrency(0);
    
    // Set current date and time
    const now = new Date();
    const dateTimeStr = now.toLocaleString('en-PH', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
    document.getElementById('saleDateTime').value = dateTimeStr;
    
    checkoutModal.show();
    
    setTimeout(() => {
        document.getElementById('customerName').focus();
    }, 500);
}

function processCheckout() {
    const total = parseFloat(document.getElementById('checkoutTotal').value);
    const received = parseFloat(document.getElementById('amountReceived').value) || 0;
    
    if (received < total) {
        showToast('Insufficient payment!', 'error');
        return;
    }
    
    const data = {
        cart: cart,
        customer_name: document.getElementById('customerName').value,
        customer_contact: document.getElementById('customerContact').value,
        customer_address: document.getElementById('customerAddress').value,
        payment_method: document.getElementById('paymentMethod').value,
        amount_received: received,
        change: received - total
    };
    
    console.log('Processing checkout with data:', data);
    
    const submitBtn = document.getElementById('submitCheckout');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    fetch('pos_api.php?action=checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Checkout response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text(); // Get as text first to see what we're receiving
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
        }
    })
    .then(data => {
        console.log('Checkout result:', data);
        if (data.success) {
            showToast('Sale completed successfully!', 'success');
            
            if (confirm('Print receipt?')) {
                window.open('receipt.php?sale_id=' + data.sale_id, '_blank');
            }
            
            cart = [];
            updateCart();
            
            checkoutModal.hide();
            document.getElementById('checkoutForm').reset();
            
            document.getElementById('barcodeInput').focus();
        } else {
            showToast(data.message || 'Checkout failed!', 'error');
        }
    })
    .catch(error => {
        console.error('Checkout error:', error);
        showToast('Error processing checkout: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Complete Sale';
    });
}

function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function playBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch(e) {
        console.log('Audio not supported');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    // If you have a toast library, use it
    // Otherwise, use alert as fallback
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            text: message,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        console.log(type.toUpperCase() + ': ' + message);
        // You can also create a simple toast div here
        alert(message);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>