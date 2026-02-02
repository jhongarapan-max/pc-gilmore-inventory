/**
 * Suppliers Module Scripts
 */

// Filter suppliers
function filterSuppliers(searchTerm) {
    const table = document.querySelector('.suppliers-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    searchTerm = searchTerm.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Add debounced search
const debouncedSupplierSearch = debounce(function(e) {
    filterSuppliers(e.target.value);
}, 300);

// Delete supplier
function deleteSupplier(supplierId, supplierName) {
    if (!confirmDelete(supplierName)) return;

    showLoading(document.querySelector(`[data-supplier-id="${supplierId}"] .delete-btn`));

    fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + supplierId
        })
        .then(response => response.text())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
}

// Initialize suppliers page
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.supplier-search');
    if (searchInput) {
        searchInput.addEventListener('input', debouncedSupplierSearch);
    }
});