/**
 * Scanner Module Scripts
 */

// Scanner input handler
let scannerBarcode = '';

function handleScannerKeypress(e) {
    if (e.key === 'Enter') {
        const barcode = e.target.value.trim();
        if (barcode) {
            scanItem(barcode);
            e.target.value = '';
        }
        return false;
    }
}

// Scan item
function scanItem(barcode) {
    fetch('scan_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=scan&barcode=' + encodeURIComponent(barcode)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayScannedItem(data.data);
            } else {
                showAlert('Item not found: ' + (data.message || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error scanning item', 'danger');
        });
}

// Display scanned item
function displayScannedItem(item) {
    const container = document.querySelector('.scanned-items');
    if (!container) return;

    const itemDiv = document.createElement('div');
    itemDiv.className = 'scanned-item fade-in';
    itemDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">${item.name}</h6>
                <small class="text-muted">Barcode: ${item.barcode}</small>
                <p class="mb-0">Stock: <strong>${item.stock}</strong></p>
            </div>
            <span class="badge bg-primary">₱${parseFloat(item.price).toFixed(2)}</span>
        </div>
    `;
    container.insertBefore(itemDiv, container.firstChild);

    // Keep only last 10 items
    const items = container.querySelectorAll('.scanned-item');
    if (items.length > 10) {
        items[items.length - 1].remove();
    }

    showAlert('Item scanned successfully', 'success');
}

// Show alert
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.scanner-container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        setTimeout(() => {
            const alert = new bootstrap.Alert(alertDiv);
            alert.close();
        }, 3000);
    }
}

// Initialize scanner page
document.addEventListener('DOMContentLoaded', function() {
    const scannerInput = document.querySelector('.scanner-input');
    if (scannerInput) {
        scannerInput.focus();
        scannerInput.addEventListener('keypress', handleScannerKeypress);
    }
});