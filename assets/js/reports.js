/**
 * Reports Module Scripts
 */

// Filter reports
function filterReports() {
    const startDate = document.querySelector('[name="start_date"]') ? .value || '';
    const endDate = document.querySelector('[name="end_date"]') ? .value || '';

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate
    });

    // Reload page with filters
    window.location.href = window.location.pathname + '?' + params.toString();
}

// Export report to CSV
function exportReport() {
    const tableId = document.querySelector('.report-table') ? .id || 'report-table';
    exportTableToCSV(tableId, 'report-' + new Date().toISOString().split('T')[0] + '.csv');
}

// Print report
function printReport() {
    printContent('report-container');
}

// Initialize reports page
document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', filterReports);
    }
});