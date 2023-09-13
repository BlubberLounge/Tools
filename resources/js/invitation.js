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
        const id = e.target.parentElement.getAttribute('data-invitation-id');
        axios.post(`/invitation/approve/${id}`).then( response =>
        {
            console.log(response);

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });

    const denieBtns = document.querySelector('.btn-denie');
    denieBtns?.addEventListener('click', e =>
    {
        console.log('denied');
        const id = e.target.parentElement.getAttribute('data-invitation-id');

        axios.post(`/invitation/denie/${id}`).then( response =>
        {
            console.log(response);

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });
});
