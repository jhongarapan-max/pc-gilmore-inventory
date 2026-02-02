/**
 * Navigation JavaScript - Sidebar and menu handling
 */

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

// Handle dropdown toggle clicks in sidebar (for both nav items and footer)
function setupDropdownHandlers() {
    const dropdownToggles = document.querySelectorAll('.sidebar .dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const parentDropdown = this.closest('.dropdown');
            if (!parentDropdown) return;
            const menu = parentDropdown.querySelector('.dropdown-menu');

            if (menu) {
                // close others
                document.querySelectorAll('.sidebar .dropdown-menu.show').forEach(other => {
                    if (other !== menu) {
                        other.classList.remove('show');
                        const otherToggle = other.closest('.dropdown').querySelector('.dropdown-toggle');
                        if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
                    }
                });

                const isOpen = menu.classList.toggle('show');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        });
    });
}

// Close dropdowns when clicking outside or on navigation (mobile)
function closeAllDropdowns(exclude) {
    document.querySelectorAll('.sidebar .dropdown-menu.show').forEach(menu => {
        if (menu !== exclude) {
            menu.classList.remove('show');
            const toggle = menu.closest('.dropdown').querySelector('.dropdown-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Open sidebar by default on larger screens
    if (window.innerWidth > 768) openNav();

    // Setup handlers
    setupDropdownHandlers();

    // Close sidebar on regular link click (mobile only)
    if (window.innerWidth <= 768) {
        const navLinks = document.querySelectorAll('.sidebar .nav-link:not(.dropdown-toggle)');
        const dropdownItems = document.querySelectorAll('.sidebar .dropdown-item');

        navLinks.forEach(link => link.addEventListener('click', closeNav));
        dropdownItems.forEach(item => item.addEventListener('click', closeNav));
    }

    // window resize handling
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById("main-menu");
        if (window.innerWidth > 768) {
            if (sidebar) {
                sidebar.style.width = "250px";
                sidebar.style.display = "flex";
                sidebar.classList.add('active');
            }
        } else {
            if (sidebar && !sidebar.classList.contains('active')) {
                sidebar.style.width = "0";
                sidebar.style.display = "none";
            }
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sidebar')) {
            closeAllDropdowns();
        }
    });
});