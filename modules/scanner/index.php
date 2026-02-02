<?php
/**
 * Barcode Scanner Page
 * Scan items to view stock information
 */
require_once '../../init.php';
requirePermission('scanner');

$pageTitle = 'Barcode Scanner';

$additionalJS = <<<'JSBLOCK'
<script>
let scanBuffer = '';
let scanTimeout;

// Focus on scanner input on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('scannerInput').focus();
    
    // Auto-focus when clicked anywhere
    document.body.addEventListener('click', function() {
        document.getElementById('scannerInput').focus();
    });
});

// Handle scanner input
document.getElementById('scannerInput').addEventListener('input', function(e) {
    clearTimeout(scanTimeout);
    
    scanTimeout = setTimeout(function() {
        const barcode = document.getElementById('scannerInput').value.trim();
        if (barcode) {
            searchItem(barcode);
        }
    }, 300);
});

// Search for item
function searchItem(barcode) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Searching...</p></div>';
    
    fetch('scan_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({barcode: barcode})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayItem(data.item);
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-x-circle" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Item Not Found</h4>
                    <p>No item found with barcode: <strong>${barcode}</strong></p>
                </div>
            `;
        }
        
        // Clear and refocus input
        document.getElementById('scannerInput').value = '';
        document.getElementById('scannerInput').focus();
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> Error searching for item.
            </div>
        `;
        document.getElementById('scannerInput').value = '';
        document.getElementById('scannerInput').focus();
    });
}

// Display item details
function displayItem(item) {
    let stockBadge = '';
    let stockClass = '';
    
    if (item.stock_quantity == 0) {
        stockBadge = '<span class="stock-badge badge bg-danger">OUT OF STOCK</span>';
        stockClass = 'border-danger';
    } else if (item.stock_quantity <= item.min_stock_level) {
        stockBadge = '<span class="stock-badge badge bg-warning text-dark">' + item.stock_quantity + ' ' + item.unit + '</span>';
        stockClass = 'border-warning';
    } else {
        stockBadge = '<span class="stock-badge badge bg-success">' + item.stock_quantity + ' ' + item.unit + '</span>';
        stockClass = 'border-success';
    }
    
    const html = '<div class="item-card ' + stockClass + ' fade-in">' +
        '<div class="row">' +
            '<div class="col-md-8">' +
                '<h3 class="mb-3">' + item.item_name + '</h3>' +
                '<p class="item-info"><strong>Barcode:</strong> <code>' + item.barcode + '</code></p>' +
                '<p class="item-info"><strong>Category:</strong> ' + (item.category_name || 'N/A') + '</p>' +
                '<p class="item-info"><strong>Description:</strong> ' + (item.description || 'N/A') + '</p>' +
                '<p class="item-info"><strong>Price:</strong> <span class="text-primary h4">' + formatCurrency(item.selling_price) + '</span></p>' +
            '</div>' +
            '<div class="col-md-4 text-center">' +
                '<div class="mt-3">' +
                    '<p class="mb-2"><strong>Stock Level</strong></p>' +
                    stockBadge +
                    '<p class="mt-3 text-muted">Min: ' + item.min_stock_level + ' ' + item.unit + '</p>' +
                '</div>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    document.getElementById('scanResult').innerHTML = html;
    
    // Play success sound (optional)
    playBeep();
}

// Format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Play beep sound
function playBeep() {
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
}

// Manual search button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchBtn').addEventListener('click', function() {
        const barcode = document.getElementById('scannerInput').value.trim();
        if (barcode) {
            searchItem(barcode);
        }
    });
    
    // Enter key to search
    document.getElementById('scannerInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            if (barcode) {
                searchItem(barcode);
            }
        }
    });
});
</script>
JSBLOCK;

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-upc-scan"></i> Barcode Scanner</h2>
        <p class="text-muted">Scan item barcode or QR code to view stock information</p>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="bi bi-upc-scan"></i> Ready to Scan</h4>
            </div>
            <div class="card-body p-4">
                <!-- Scanner Input -->
                <div class="mb-4">
                    <label for="scannerInput" class="form-label h5">Scan Barcode</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" 
                               class="form-control scanner-input" 
                               id="scannerInput" 
                               placeholder="Point scanner here or type barcode..." 
                               autocomplete="off">
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        USB barcode scanners work as keyboard input - just scan!
                    </small>
                </div>
                
                <!-- Scan Result -->
                <div id="scanResult" class="mt-4">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-upc-scan" style="font-size: 5rem; opacity: 0.3;"></i>
                        <p class="mt-3">Waiting for barcode scan...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-question-circle"></i> How to Use</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-1-circle"></i> USB Barcode Scanner</h6>
                        <p>Just point and scan - it works like a keyboard!</p>
                        
                        <h6 class="mt-3"><i class="bi bi-2-circle"></i> Manual Entry</h6>
                        <p>Type the barcode and press Enter or click Search.</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-3-circle"></i> View Results</h6>
                        <p>Item details and stock level appear instantly.</p>
                        
                        <h6 class="mt-3"><i class="bi bi-4-circle"></i> Scan Next</h6>
                        <p>Input clears automatically - ready for next scan!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
