/**
 * @author Maximilian Mewes
 * @section Authentification
 *
 */

document.addEventListener('DOMContentLoaded', function()
{
    let isPasswordHidden = true;
    const toggler = document.getElementById('password-toggler');
    const passwordField = document.getElementById('password');

    if (toggler && passwordField) {
        const showPassword = () => {
            if(isPasswordHidden) {
                passwordField.setAttribute('type', 'text');
                isPasswordHidden = false;
            }
        };

        const hidePassword = () => {
            if(!isPasswordHidden) {
                passwordField.setAttribute('type', 'password');
                isPasswordHidden = true;
            }
        };

        toggler.addEventListener('mousedown', showPassword);
        toggler.addEventListener('touchstart', showPassword);
        toggler.addEventListener('mouseup', hidePassword);
        toggler.addEventListener('mouseleave', hidePassword);
        toggler.addEventListener('touchend', hidePassword);
    }
});
