/**
 * @author Maximilian Mewes
 * @section Authentification
 *
 */

$(function()
{
    let isPasswordHidden = true;

    $('#password-toggler').on('mousedown touchstart', e =>
    {
        if(isPasswordHidden) {
            $('#password').attr('type', 'text');
            isPasswordHidden = false;
        }

    }).bind('mouseup mouseleave touchend', e =>
    {
        if(!isPasswordHidden) {
            $('#password').attr('type', 'password');
            isPasswordHidden = true;
        }
    });
});
