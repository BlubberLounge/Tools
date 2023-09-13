/**
 * @author Maximilian Mewes
 *
 *
 */

$(function()
{
    const approveBtns = document.querySelector('.btn-approve');
    approveBtns?.addEventListener('click', e =>
    {
        console.log('approved');
        var id = e.target.parentElement.getAttribute('data-invitation-id');

        if(id)
            axios.post(`/invitation/approve/${id}`).catch(function (error) {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
    });

    const denieBtns = document.querySelector('.btn-denie');
    denieBtns?.addEventListener('click', e =>
    {
        console.log('denied');
        var id = e.target.parentElement.getAttribute('data-invitation-id');

        if(id)
            axios.post(`/invitation/denie/${id}`).catch(function (error) {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
    });
});
