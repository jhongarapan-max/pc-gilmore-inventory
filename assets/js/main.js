/**
 * Main JavaScript Functions
 */

// Barcode scanner input handler
let scannerTimeout;
let scannedBarcode = '';

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Format currency inputs
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value) || 0;
            this.value = value.toFixed(2);
        });
    });

    // Open sidebar by default on larger screens
    if (window.innerWidth > 768) {
        openNav();
    }
});

// Confirmation dialog
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to perform this action?');
}

// Delete confirmation
function confirmDelete(itemName) {
    return confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`);
}

// Format currency
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Calculate total
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.item-subtotal').forEach(function(el) {
        total += parseFloat(el.textContent.replace(/[₱,]/g, '')) || 0;
    });
    return total;
}

// Show loading spinner
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
        element.disabled = true;
    }
}

// Hide loading spinner
function hideLoading(element, originalText) {
    if (element) {
        element.innerHTML = originalText;
        element.disabled = false;
    }
}

// Print function
function printContent(elementId) {
    const content = document.getElementById(elementId);
    if (content) {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<style>body{padding:20px;} @media print{.no-print{display:none;}}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(content.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }

        csv.push(row.join(','));
    }

    downloadCSV(csv.join('\n'), filename);
}

// Download CSV file
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// AJAX request helper
function ajaxRequest(url, method, data, successCallback, errorCallback) {
    fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: method !== 'GET' ? JSON.stringify(data) : null
        })
        .then(response => response.json())
        .then(data => {
            if (successCallback) successCallback(data);
        })
        .catch(error => {
            console.error('Error:', error);
            if (errorCallback) errorCallback(error);
        });
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '11';
        document.body.appendChild(container);
    }

    const bgClass = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    }[type] || 'bg-info';

    const toast = document.createElement('div');
    toast.className = `toast ${bgClass} text-white`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="toast-body">
            ${message}
            <button type="button" class="btn-close btn-close-white float-end" data-bs-dismiss="toast"></button>
        </div>
    `;

    document.getElementById('toastContainer').appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Validate form
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }

    return true;
}

// Number input only
function numbersOnly(event) {
    const charCode = event.which ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/* Sidebar Toggle */
function openNav() {
    const sidebar = document.getElementById("main-menu");
    if (sidebar) {
        sidebar.classList.add('active');
        sidebar.style.width = "250px";
        sidebar.style.display = "flex";
    }
}

function closeNav() {
    const sidebar = document.getElementById("main-menu");
    if (sidebar) {
        sidebar.classList.remove('active');
        if (window.innerWidth <= 768) {
            sidebar.style.width = "0";
            sidebar.style.display = "none";
        }
    }
}

// Close sidebar when clicking on a navigation link (mobile)
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth <= 768) {
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeNav();
            });
        });
    }
});