
document.getElementById('btnQueueAdd').addEventListener('click', e =>
{
    axios.post(`/api/v1/dart/queue/add`).then( response => {
        const newItem = document.createElement('li');
        newItem.innerHTML = 'Ich';
        newItem.classList.add('list-group-item');

        const timeText = document.createElement('span');
        timeText.innerHTML = ' grade eben';
        timeText.classList.add('text-body-secondary', 'small');
        newItem.appendChild(timeText);

        const btnRemove = document.createElement('button');
        btnRemove.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        btnRemove.id = 'btnQueueRemove';
        btnRemove.classList.add('btn', 'p-0', 'text-danger', 'h-100', 'float-end');
        newItem.appendChild(btnRemove);
        addRemoveEventListener(btnRemove);

        document.getElementById('dartQueueList').append(newItem);
        const dartText = document.getElementById('dartQueueText')
        dartText.innerHTML = 'Du bist in der Warteschlange';
        dartText.classList.remove('text-danger');
        dartText.classList.add('text-success');

        document.getElementById('btnQueueAdd').classList.add('disabled');
    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }
    });
});

addRemoveEventListener(document.getElementById('btnQueueRemove'));


function addRemoveEventListener(element)
{
    element.addEventListener('click', e =>
    {
        axios.post(`/api/v1/dart/queue/remove`).then( response => {
            e.target.closest('li').remove();

            const dartText = document.getElementById('dartQueueText')
            dartText.innerHTML = 'Du bist nicht in der Warteschlange';
            dartText.classList.remove('text-success');
            dartText.classList.add('text-danger');

            document.getElementById('btnQueueAdd').classList.remove('disabled');

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });
}
