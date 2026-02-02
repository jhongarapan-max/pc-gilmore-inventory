/**
 * Stock Module Scripts
 */

// Add stock
function addStock() {
    const form = document.querySelector('.stock-form');
    if (!form) return;

    const formData = new FormData(form);
    showLoading(document.querySelector('.submit-btn'));

    fetch('stock_in.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Reload page to show updated stock
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        })
        .finally(() => {
            hideLoading(document.querySelector('.submit-btn'), 'Add Stock');
        });
}

// Remove stock
function removeStock() {
    const form = document.querySelector('.stock-form');
    if (!form) return;

    const formData = new FormData(form);
    showLoading(document.querySelector('.submit-btn'));

    fetch('stock_out.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Reload page to show updated stock
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        })
        .finally(() => {
            hideLoading(document.querySelector('.submit-btn'), 'Remove Stock');
        });
}

// Filter stock history
function filterHistory() {
    const itemId = document.querySelector('[name="item_id"]') ? .value || '';
    const type = document.querySelector('[name="type"]') ? .value || '';

    const params = new URLSearchParams();
    if (itemId) params.append('item_id', itemId);
    if (type) params.append('type', type);

    window.location.href = 'history.php?' + params.toString();
}

// Initialize stock page
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', filterHistory);
    }
});