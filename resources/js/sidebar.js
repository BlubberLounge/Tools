/**
 * Sidebar functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarCollapse');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Language selector
    const languageInputs = document.querySelectorAll('#language-selector input[type=radio]');
    const localeForm = document.getElementById('form-locale-selector');

    languageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            if (localeForm) {
                localeForm.submit();
            }
        });
    });

    // Submenu collapse functionality
    document.querySelectorAll('[data-collapse-toggle]').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-collapse-toggle');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.toggle('hidden');
                target.classList.toggle('block');
            }
        });
    });
});
