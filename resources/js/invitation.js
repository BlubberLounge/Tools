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
        let element = e.target;
        if(element.tagName == 'I')
            element = element.parentElement;

        console.log('approved');
        const id = element.getAttribute('data-invitation-id');
        console.log(element);
        updateStatus('approve', id);
        element.parentNode.innerHTML = getPill('approved', 'var(--bl-clr-green)');
    });

    const denieBtns = document.querySelectorAll('.btn-denie');
    addEventListenerList(denieBtns, 'click', e =>
    {
        let element = e.target;
        if(element.tagName == 'I')
            element = element.parentElement;

        console.log('denied');
        const id = element.getAttribute('data-invitation-id');
        console.log(element);
        updateStatus('denie', id);
        element.parentNode.innerHTML = getPill('denied', 'var(--bl-clr-red)');
    });
});

function addEventListenerList(list, event, cb)
{
    for (var i = 0;i < list.length; i++)
        list[i].addEventListener(event, cb);
}

function getPill(text, color)
{
    return `<span class="badge" style="background-color:${color}">${text}</span>`
}

async function updateStatus(status, id)
{
    axios.post(`/invitation/${status}/${id}`).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }
    });
}
