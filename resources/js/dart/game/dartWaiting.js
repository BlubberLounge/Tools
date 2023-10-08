/**
 * @author Maximilian Mewes
 *
 *
 */

import PlayerStatus from "../enums/playerStatus";

$(function()
{
    const gameId = document.getElementById('gameId').value;
    const intervalSec = 30;

    fetchPlayersStatus(gameId);

    const fetchInterval = setInterval(function()
    {
        fetchPlayersStatus(gameId);
    }, intervalSec * 1000);

    document.getElementById('btnRefresh').addEventListener('click', e => {
        e.target.disabled = true;
        setTimeout(function() {
            e.target.disabled = false;
        }, 10000);
        fetchPlayersStatus(gameId);
    });
});

async function fetchPlayersStatus(gameId)
{
    axios.get(`/api/v1/dart/getPlayerStatus/${gameId}`).then( response =>
        {
            updateDisplay(gameId, response.data.data.user);
        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
    });
}

function updateDisplay(gameId, data)
{
    let acceptedCount = 0;

    data.forEach(user => {
        const id = user.user_id;
        const status = PlayerStatus.fromString(user.status);
        const parent = document.querySelector('[data-user-id="'+user.user_id+'"]');
        const el = parent.getElementsByClassName('user-status')[0];
        el.innerHTML = `${status} `;
        parent.classList.remove('border-success', 'border-danger');
        console.log(status);
        if(status == PlayerStatus.denied) {
            const btn = document.createElement('a');
            btn.href = '#';
            btn.innerHTML = 'kick';
            btn.addEventListener('click', e => {
                kickPlayer(gameId, id);
                location.reload();
            });

            el.appendChild(btn);
            parent.classList.add('border-danger');

        } else if(status == PlayerStatus.accepted) {
            acceptedCount++;
            parent.classList.add('border-success');
        }
    });

    if(acceptedCount == data.length)
        location.reload();
}

function kickPlayer(gameId, id)
{
    axios.delete(`/api/v1/dart/destroyPlayer/${gameId}/user/${id}`).then( response =>
    {
        // console.log(response);
    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }
    });
}
