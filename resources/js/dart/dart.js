/**
 * @author Maximilian Mewes
 *
 *
 */

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import PlayerList from './playerList';
import Settings from './settings';

/**
 * Singleton Dart entry class
 *
 */
export default class Dart
{
    constructor(boardContainer = null)
    {
        if(Dart.instance)
            return Dart.instance;

        Dart.instance = this;

        this.boardContainer = boardContainer;
        this.playerList = new PlayerList();
        this.settings = new Settings();

        this.initialized = false;
    }

    init()
    {
        if(this.initialized)
            return;

        console.log('Dart initializing');

        this.dartboard = new Dartboard(this.boardContainer);
        this.dartboard.render();
        this.playerList.lock();
        this.playerList.sortByPosition();

        this.addListeners();

        this.initialized = true;
    }

    addListeners()
    {
        document.querySelector(this.boardContainer).addEventListener('dartHit', h =>
        {
            const hit = h.detail;
            this.placeHitMarker(hit);
        });
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
        var newDiv = () => document.createElement("div");

        usersList.players.forEach(e =>
        {
            let row = document.createElement("li");
            row.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');

            let rowData = newDiv();
            rowData.classList.add('me-auto');
            rowData.innerHTML = e.name;

            let addButton = document.createElement('button');
            addButton.classList.add('btn', 'btn-add-player');
            addButton.setAttribute('style', 'color:var(--bs-success)');
            addButton.addEventListener('click', h =>
            {
                this.addPlayer(e);

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

        let colName = newDiv();
        colName.classList.add('col', 'fs-5');
        colName.setAttribute('data-user-id', addedUser.id);
        colName.innerHTML = addedUser.name;
        row.appendChild(colName);

        let colActions = newDiv();
        colActions.classList.add('col-auto');
        // colActions.innerHTML = '<i class="fa-solid fa-circle-up fa-xl me-3"></i>\n<i class="fa-solid fa-circle-down fa-xl"></i>';

        let addButton = document.createElement('button');
        addButton.classList.add('btn', 'btn-add-player');
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

    placeHitMarker(hit)
    {
        console.log(hit);
        console.log(this.playerList.players);
        let hitMarker = document.createElement("i");
        hitMarker.classList.add('hitMarker');

        let topPosition = hit.y - (8/2); // center hitMarker on cursor tip
        let leftPosition = hit.x - (8/2); // center hitMarker on cursor tip

        hitMarker.style.setProperty("top", topPosition+"px");
        hitMarker.style.setProperty("left", leftPosition+"px");
        // console.log(UTILS.cartesian2Polar(hit.x-200, hit.y-200));

        document.getElementById('dartboard').append(hitMarker);

        return hitMarker;
    }
}
