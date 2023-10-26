
const switchList = document.querySelectorAll('.form-check-input[type="checkbox"]');
[...switchList].map( switchElement => switchElement.addEventListener('change', e => {
    updateSettings(e.target,
    {
        id: e.target.id,
        value: e.target.checked
    });
}));

async function updateSettings(element, data)
{
    await axios.put('/api/v1/user/updateSettings', data).then( r => {
        element.classList.add('success');

        setTimeout(function() {
            element.classList.remove('success');
        }, 2000);

    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }

        // notifiy user something went wrong
        element.classList.add('error');

        // reset switch
        setTimeout(function()
        {
            if(data.value == 1) {
                element.checked = false;
            } else if(data.value == 0) {
                element.checked = true;
            }
            element.classList.remove('error');
        }, 2000);
    });
}
