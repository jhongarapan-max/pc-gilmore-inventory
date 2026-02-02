/**
 * Inventory Module Scripts
 */

// Search and filter functionality
function filterInventory(searchTerm) {
    const table = document.querySelector('.inventory-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    searchTerm = searchTerm.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Add debounced search
const debouncedSearch = debounce(function(e) {
    filterInventory(e.target.value);
}, 300);

// Initialize inventory page
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.inventory-search');
    if (searchInput) {
        searchInput.addEventListener('input', debouncedSearch);
    }
});