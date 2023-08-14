/**
 *
 *
 *
 */

$(function()
{
    $('#language-selector input[type=radio]').on('change', e =>
    {
        $('#form-locale-selector').submit();
    });
});
