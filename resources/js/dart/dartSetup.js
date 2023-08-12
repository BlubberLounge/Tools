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
        let result = (await axios.get('/api/v1/user/search/'+name)).data;

        let fetchedUsersList = new PlayerList(result.data.users);
        this.populateUserList(fetchedUsersList);
    }

    populateUserList(usersList)
    {
        let list = document.querySelector('#ListUser');
        list.innerHTML = '';

        var totalHeight = 0;
        var newDiv = () => document.createElement('div');
        var newBtn = () =>
        {
            let btn = document.createElement('button');
            btn.setAttribute('type', 'button');
            return btn;
        }

        usersList.players.forEach(user =>
        {
            let row = document.createElement('li');
            row.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let rowData = newDiv();
            rowData.classList.add('me-auto');
            rowData.innerHTML = user.name;

            let addButton = newBtn();
            addButton.classList.add('btn', 'btn-add-player');
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
        let newBtn = () =>
        {
            let btn = document.createElement('button');
            btn.setAttribute('type', 'button');
            return btn;
        }

        let hr = document.createElement('hr');
        hr.setAttribute('style', 'margin: .5rem 0');
        list.appendChild(hr);
        let hrStyles = window.getComputedStyle(hr);
        let hrHeight = hr.offsetHeight + parseInt(hrStyles.getPropertyValue('margin-top')) + parseInt(hrStyles.getPropertyValue('margin-bottom')) ;

        let row = newDiv();
        row.classList.add('row');
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

        let removeButton = newBtn();
        removeButton.classList.add('btn', 'btn-remove-player');
        removeButton.addEventListener('click', h =>
        {
            this.playerList.remove(addedUser);

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

        let xmarkIcon = document.createElement('i');
        xmarkIcon.classList.add('fa-solid', 'fa-square-xmark', 'fa-xl');
        removeButton.appendChild(xmarkIcon);
        removeButton.innerHTML = "\n" + removeButton.innerHTML;

        // let upButton = newBtn();
        // upButton.classList.add('btn', 'btn-up-player');
        // upButton.addEventListener('click', h =>
        // {
        //     this.playerList.moveUp(addedUser);

            // let el = h.target.closest('div.row');
            // el.style.transition = "opacity .5s ease";
            // el.style.opacity = 0;

            // let hr = el.previousElementSibling;
            // console.log(hr);
            // hr.style.transition = "opacity .2s ease";
            // hr.style.opacity = 0;

            // setTimeout(function(){ el.parentNode.removeChild(el)}, 500);
            // setTimeout(function(){ hr.parentNode.removeChild(hr)}, 200);

            // window.scrollTo(0, document.documentElement.scrollTop - row.offsetHeight - hrHeight);
        // });

        // let upIcon = document.createElement('i');
        // upIcon.classList.add('fa-solid', 'fa-circle-up', 'fa-xl');
        // upButton.appendChild(upIcon);
        // upButton.innerHTML = "\n" + upButton.innerHTML;

        // let downButton = newBtn();
        // downButton.classList.add('btn', 'btn-down-player');
        // downButton.addEventListener('click', h =>
        // {
        //     this.playerList.remove(addedUser);

        //     let el = h.target.closest('div.row');
        //     el.style.transition = "opacity .5s ease";
        //     el.style.opacity = 0;

        //     let hr = el.previousElementSibling;
        //     console.log(hr);
        //     hr.style.transition = "opacity .2s ease";
        //     hr.style.opacity = 0;

        //     setTimeout(function(){ el.parentNode.removeChild(el)}, 500);
        //     setTimeout(function(){ hr.parentNode.removeChild(hr)}, 200);

        //     window.scrollTo(0, document.documentElement.scrollTop - row.offsetHeight - hrHeight);
        // });

        // let downIcon = document.createElement('i');
        // downIcon.classList.add('fa-solid', 'fa-circle-down', 'fa-xl');
        // downButton.appendChild(downIcon);
        // downButton.innerHTML = "\n" + downButton.innerHTML;


        colActions.appendChild(removeButton);
        // colActions.appendChild(upButton);
        // colActions.appendChild(downButton);

        row.appendChild(colActions);

        list.appendChild(row);
        window.scrollTo(0, row.offsetHeight + hrHeight + document.documentElement.scrollTop);
        setTimeout(function() { row.style.opacity = 1; }, 200);
    }
}
