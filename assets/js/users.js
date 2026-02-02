/**
 * Users Module Scripts
 */

// Filter users
function filterUsers(searchTerm) {
    const table = document.querySelector('.users-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    searchTerm = searchTerm.toLowerCase();

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Add debounced search
const debouncedUserSearch = debounce(function(e) {
    filterUsers(e.target.value);
}, 300);

// Delete user
function deleteUser(userId, userName) {
    if (!confirmDelete(userName)) return;

    showLoading(document.querySelector(`[data-user-id="${userId}"] .delete-btn`));

    fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + userId
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

// Reset user password
function resetPassword(userId, userName) {
    const newPassword = prompt(`Enter new password for ${userName}:`);
    if (!newPassword) return;

    showLoading(document.querySelector(`[data-user-id="${userId}"] .reset-btn`));

    fetch('edit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + userId + '&action=reset_password&password=' + encodeURIComponent(newPassword)
        })
        .then(response => response.text())
        .then(data => {
            alert('Password reset successfully');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
}

// Initialize users page
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.user-search');
    if (searchInput) {
        searchInput.addEventListener('input', debouncedUserSearch);
    }
});