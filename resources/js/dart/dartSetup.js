/**
 * @author Maximilian Mewes
 *
 *
 */

import PlayerList from './playerList';

/**
 * Singleton Dart entry class
 *
 */
export default class DartSetup
{
    constructor()
    {
        if(DartSetup.instance)
            return DartSetup.instance;

        DartSetup.instance = this;

        this.playerList = new PlayerList();
    }

    async searchUser(name)
    {
        let result = (await axios({
            method: 'get',
            url: '/api/v1/user/search/'+name,
        })).data;

        let fetchedUsersList = new PlayerList(result.data.users);
        this.populateUserList(fetchedUsersList);
    }

    populateUserList(usersList)
    {
        let list = document.querySelector('#ListUser');
        list.innerHTML = '';

        var totalHeight = 0;
        var newDiv = () => document.createElement('div');

        usersList.players.forEach(user =>
        {
            let row = document.createElement('li');
            row.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let rowData = newDiv();
            rowData.classList.add('me-auto');
            rowData.innerHTML = user.name;

            let addButton = document.createElement('button');
            addButton.classList.add('btn', 'btn-add-player');
            addButton.setAttribute('type', 'button');
            addButton.setAttribute('style', 'color:var(--bs-success)');
            addButton.addEventListener('click', h =>
            {
                this.addPlayer(user);

                let el = h.target.closest('li');
                el.style.transition = "opacity .5s ease";
                el.style.opacity = 0;

                setTimeout(function(){ el.parentNode.removeChild(el)}, 500);
            });

            let plusIcon = document.createElement('i');
            plusIcon.classList.add('fa-solid', 'fa-circle-plus', 'fa-xl');
            addButton.appendChild(plusIcon);

            row.appendChild(rowData);
            row.appendChild(addButton);

            list.appendChild(row);
            totalHeight += row.offsetHeight;
        });

        let currentY = document.documentElement.scrollTop;
        window.scrollTo(0, totalHeight + currentY); // better UX for mobile screens
    }

    addPlayer(id)
    {
        let addedUser = this.playerList.add(id);

        let list = document.querySelector('#selectedUserList');
        let newDiv = () => document.createElement("div");

        let hr = document.createElement('hr');
        hr.setAttribute('style', 'margin: .5rem 0');
        list.appendChild(hr);
        let hrStyles = window.getComputedStyle(hr);
        let hrHeight = hr.offsetHeight + parseInt(hrStyles.getPropertyValue('margin-top')) + parseInt(hrStyles.getPropertyValue('margin-bottom')) ;

        let row = newDiv();
        row.classList.add('row', 'd-flex', 'justify-content-between', 'align-items-center');
        row.style.opacity = 0;
        row.style.transition = "opacity .5s ease";

        let inputHidden = document.createElement('input');
        inputHidden.setAttribute('type', 'hidden');
        inputHidden.setAttribute('name', 'users['+addedUser.id+']');
        inputHidden.setAttribute('value', addedUser.id);
        row.appendChild(inputHidden);

        let colName = newDiv();
        colName.classList.add('col', 'fs-5');
        colName.setAttribute('data-user-id', addedUser.id);
        colName.innerHTML = addedUser.fullName;
        row.appendChild(colName);

        let colActions = newDiv();
        colActions.classList.add('col-auto');
        // colActions.innerHTML = '<i class="fa-solid fa-circle-up fa-xl me-3"></i>\n<i class="fa-solid fa-circle-down fa-xl"></i>';

        let addButton = document.createElement('button');
        addButton.classList.add('btn', 'btn-add-player');
        addButton.setAttribute('type', 'button');
        addButton.setAttribute('style', 'color:var(--bs-danger)');
        addButton.addEventListener('click', h =>
        {
            this.removePlayer(addedUser);

            let el = h.target.closest('div.row');
            el.style.transition = "opacity .5s ease";
            el.style.opacity = 0;

            let hr = el.previousElementSibling;
            console.log(hr);
            hr.style.transition = "opacity .2s ease";
            hr.style.opacity = 0;

            setTimeout(function(){ el.parentNode.removeChild(el)}, 500);
            setTimeout(function(){ hr.parentNode.removeChild(hr)}, 200);

            window.scrollTo(0, document.documentElement.scrollTop - row.offsetHeight - hrHeight);
        });
        let plusIcon = document.createElement('i');
        plusIcon.classList.add('fa-solid', 'fa-square-xmark', 'fa-xl');
        addButton.appendChild(plusIcon);

        colActions.appendChild(addButton);

        row.appendChild(colActions);

        list.appendChild(row);
        window.scrollTo(0, row.offsetHeight + hrHeight + document.documentElement.scrollTop);
        setTimeout(function() { row.style.opacity = 1; }, 200);
    }

    removePlayer(user)
    {
        console.log(this.playerList.players);
        this.playerList.remove(user);
        console.log(this.playerList.players);

    }
}
