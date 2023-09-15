/**
 * @author Maximilian Mewes
 *
 *
 */

$(function()
{
    const approveBtns = document.querySelectorAll('.btn-approve');
    addEventListenerList(approveBtns, 'click', e =>
    {
        console.log('approved');
        const id = e.target.parentElement.getAttribute('data-invitation-id');
        updateStatus('approve', id);
    });

    const denieBtns = document.querySelectorAll('.btn-denie');
    addEventListenerList(denieBtns, 'click', e =>
    {
        console.log('denied');
        const id = e.target.parentElement.getAttribute('data-invitation-id');
        updateStatus('denie', id);
    });
});

function addEventListenerList(list, event, cb)
{
    for (var i = 0;i < list.length; i++)
        list[i].addEventListener(event, cb);
}

async function updateStatus(status, id)
{
    axios.post(`/invitation/${status}/${id}`).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }
    });
}
