/**
 * POS Module Scripts
 */

// Scanner input handler for POS
let scannerTimeout;
let scannedBarcode = '';

function handleScannerInput(e) {
    if (e.key === 'Enter') {
        const barcode = e.target.value.trim();
        if (barcode) {
            addItemToCart(barcode);
            e.target.value = '';
        }
        return false;
    }
}

// Add item to cart via AJAX
function addItemToCart(barcode) {
    showLoading(document.querySelector('.add-btn'));

    fetch('pos_api.php?action=getItem', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCart(data.item);
                updateTotal();
            } else {
                alert('Item not found: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            hideLoading(document.querySelector('.add-btn'), 'Add Item');
        });
}

// Update cart display
function updateCart(item) {
    const cartTable = document.querySelector('.cart-table tbody');
    if (!cartTable) return;

    // Check if item already exists
    const existingRow = cartTable.querySelector(`tr[data-item-id="${item.item_id}"]`);

    if (existingRow) {
        // Update quantity
        const qtyInput = existingRow.querySelector('.item-qty');
        qtyInput.value = parseInt(qtyInput.value) + 1;
        updateLineTotal(existingRow);
    } else {
        // Add new row
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', item.item_id);
        row.setAttribute('data-price', item.selling_price);
        row.innerHTML = `
            <td>${item.item_name}</td>
            <td>₱${parseFloat(item.selling_price).toFixed(2)}</td>
            <td><input type="number" class="form-control item-qty" value="1" min="1" onchange="updateLineTotal(this.closest('tr'))"></td>
            <td class="item-subtotal">₱${parseFloat(item.selling_price).toFixed(2)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeFromCart(this)">Remove</button></td>
        `;
        cartTable.appendChild(row);
    }
}

// Update line total
function updateLineTotal(row) {
    const price = parseFloat(row.getAttribute('data-price'));
    const qty = parseInt(row.querySelector('.item-qty').value);
    const subtotal = price * qty;
    row.querySelector('.item-subtotal').textContent = '₱' + subtotal.toFixed(2);
    updateTotal();
}

// Remove item from cart
function removeFromCart(btn) {
    btn.closest('tr').remove();
    updateTotal();
}

function calculateCartTotal() {
    let total = 0;
    document.querySelectorAll('.cart-table tbody tr').forEach(row => {
        const subtotalText = row.querySelector('.item-subtotal').textContent;
        total += parseFloat(subtotalText.replace('₱', ''));
    });
    return total;
}

// Update total
function updateTotal() {
    const total = calculateCartTotal();
    const totalElement = document.querySelector('.cart-total');
    if (totalElement) {
        totalElement.textContent = formatCurrency(total);
    }
}

// Checkout
function checkout() {
    const cart = [];
    document.querySelectorAll('.cart-table tbody tr').forEach(row => {
        const item = {
            item_id: row.getAttribute('data-item-id'),
            item_name: row.cells[0].textContent,
            price: parseFloat(row.getAttribute('data-price')),
            quantity: parseInt(row.querySelector('.item-qty').value)
        };
        cart.push(item);
    });

    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }

    // You can add more customer details here if needed
    const customer_name = 'Walk-in Customer'; 
    const payment_method = 'cash';
    const amount_received = calculateCartTotal(); // Assuming exact payment for now

    showLoading(document.querySelector('.checkout-btn'));

    fetch('pos_api.php?action=checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart: cart,
            customer_name: customer_name,
            payment_method: payment_method,
            amount_received: amount_received,
            change: 0 // Assuming no change for now
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.cart-table tbody').innerHTML = '';
            updateTotal();
            alert('Transaction completed. Sale Number: ' + data.sale_number);
            // If you have a receipt page, you can redirect here:
            // window.open('receipt.php?sale_id=' + data.sale_id, '_blank');
        } else {
            alert('Checkout failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during checkout');
    })
    .finally(() => {
        hideLoading(document.querySelector('.checkout-btn'), 'Checkout');
    });
}

// Initialize POS page
document.addEventListener('DOMContentLoaded', function() {
    const scannerInput = document.querySelector('.scanner-input');
    if (scannerInput) {
        scannerInput.focus();
        scannerInput.addEventListener('keypress', handleScannerInput);
    }
});