/**
 * Sidebar functionality
 */

document.addEventListener('DOMContentLoaded', function() {
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
});
