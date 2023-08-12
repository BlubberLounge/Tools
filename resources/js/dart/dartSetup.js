/**
 * @author Maximilian Mewes
 *
 *
 */

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

        const form = document.getElementById('dartGameCreateForm');
        form.addEventListener("submit", e =>
        {
            // e.preventDefault();
            const selectedUserList = Array.prototype.slice.call(document.getElementById('selectedUserList').children);
            selectedUserList.forEach( (el, i) => {
                const userId = el.querySelector('input[type=hidden]').getAttribute('value');
                const index = i + 1;
                form.appendChild(this._createHiddenPositionInput(userId, index));
            });
        })

        document.querySelector('.btn-remove-player').addEventListener('click', h =>
        {
            this._animateCSS(h.target.closest('div.row'), 'fadeOut', .5);

            window.scrollTo(0, document.documentElement.scrollTop - 20);
        });

        document.querySelector('.btn-down-player').addEventListener('click', h =>
        {
            this._moveElementDown(h.target.closest('div.row'));
        });
    }

    async fetchUser(name)
    {
        let result = (await axios.get('/api/v1/user/search/'+name)).data.data;
        console.log(result);
        this.populateSelectionList(result.users);
    }

    populateSelectionList(users)
    {
        var totalHeight = 0;

        let list = document.querySelector('#ListUser');
        list.innerHTML = '';

        let selectedUser = [];
        const selectedUserList = Array.prototype.slice.call(document.getElementById('selectedUserList').children);
        selectedUserList.forEach( el => {
            let userId = el.querySelector('input[type=hidden]').getAttribute('value');
            selectedUser.push(parseInt(userId));
        });

        users.forEach(user =>
        {
            // not the most efficient think to do but it works for now
            if(!selectedUser.includes(user.id)) {
                const row = this._createSelectionRow(user.id, user.name);
                list.appendChild(row);
                totalHeight += row.offsetHeight;
            }
        });

        let currentY = document.documentElement.scrollTop;
        window.scrollTo(0, totalHeight + currentY); // better UX for mobile screens
    }

    _createSelectionRow(userId, userName)
    {
        let row = document.createElement('li');
        row.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
        row.setAttribute('data-user-id', userId);

        let rowData = document.createElement('div');
        rowData.classList.add('me-auto');
        rowData.innerHTML = userName;

        let addButton = document.createElement('button');
        addButton.setAttribute('type', 'button');
        addButton.classList.add('btn', 'btn-add-player');
        addButton.addEventListener('click', h =>
        {
            this._createSelectedRow(h.target.closest('li'));
            this._animateCSS(h.target.closest('li'), 'fadeOut', .5);
        });

        let plusIcon = document.createElement('i');
        plusIcon.classList.add('fa-solid', 'fa-circle-plus', 'fa-xl');
        addButton.appendChild(plusIcon);

        row.appendChild(rowData);
        row.appendChild(addButton);

        return row;
    }

    _createSelectedRow(selectionRow)
    {
        let list = document.querySelector('#selectedUserList');

        let userName = selectionRow.firstChild.innerHTML;
        let userId = selectionRow.getAttribute('data-user-id');

        // Row
        let row = document.createElement("div");
        row.classList.add('row');
        row.style.opacity = 0;
        row.style.transition = "opacity .5s ease";
        row.appendChild(this._createHiddenIDInput(userId));

        // Username column
        let colName = document.createElement("div");
        colName.classList.add('col', 'fs-5');
        colName.innerHTML = userName;
        row.appendChild(colName);

        // Action column
        const colActions = document.createElement("div");
        colActions.classList.add('col-auto', 'actions');

        // Action buttons
        const removeButton = this._createRemoveButton(row.offsetHeight);
        colActions.appendChild(removeButton);

        const upButton = this._createUpButton();
        colActions.appendChild(upButton);

        if(list.children.length > 1) {
            const downButton = this._createDownButton();
            const actions = list.children[list.children.length-1].querySelector('.actions');
            actions.insertBefore(downButton, actions.children[actions.children.length-1]);
        }


        row.appendChild(colActions);
        list.appendChild(row);

        window.scrollTo(0, row.offsetHeight + document.documentElement.scrollTop);
        setTimeout(function() { row.style.opacity = 1; }, 200);
    }

    _createHiddenIDInput(userId)
    {
        let inputHidden = document.createElement('input');
        inputHidden.setAttribute('type', 'hidden');
        inputHidden.setAttribute('name', `users[${userId}]`);
        inputHidden.setAttribute('value', userId);
        return inputHidden;
    }

    _createHiddenPositionInput(userId, position)
    {
        let inputHidden = document.createElement('input');
        inputHidden.setAttribute('type', 'hidden');
        inputHidden.setAttribute('name', `userPositions[${userId}]`);
        inputHidden.setAttribute('value', `${position}`);
        return inputHidden;
    }

    _createRemoveButton(rowHeight)
    {
        let btn = document.createElement('button');
        btn.setAttribute('type', 'button');
        btn.classList.add('btn', 'btn-remove-player');
        btn.addEventListener('click', h =>
        {
            this._animateCSS(h.target.closest('div.row'), 'fadeOut', .5);

            window.scrollTo(0, document.documentElement.scrollTop - rowHeight);
        });

        let icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-square-xmark', 'fa-xl');
        btn.appendChild(icon);
        btn.innerHTML = "\n" + btn.innerHTML;

        return btn;
    }

    _createUpButton()
    {
        let btn = document.createElement('button');
        btn.setAttribute('type', 'button');
        btn.classList.add('btn', 'btn-up-player');
        btn.addEventListener('click', h =>
        {
            this._moveElementUp(h.target);
        });

        let icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-circle-up', 'fa-xl');
        btn.appendChild(icon);
        btn.innerHTML = "\n" + btn.innerHTML;

        return btn;
    }

    _createDownButton()
    {
        let btn = document.createElement('button');
        btn.setAttribute('type', 'button');
        btn.classList.add('btn', 'btn-down-player');
        btn.addEventListener('click', h =>
        {
            this._moveElementDown(h.target);
        });

        let icon = document.createElement('i');
        icon.classList.add('fa-solid', 'fa-circle-down', 'fa-xl');
        btn.appendChild(icon);
        btn.innerHTML = "\n" + btn.innerHTML;

        return btn;
    }

    _moveElementUp(element)
    {
        const row = element.closest('div.row');
        const list = document.getElementById('selectedUserList');
        const index = this._listIndexOf(list, row);
        this._moveListElement(list, index, index-1);

        if(index-1 <= 0) {
            row.querySelector('.btn-up-player').remove();
            const actions = list.children[1].querySelector('.actions');
            actions.insertBefore(this._createUpButton(), actions.children[1]);
        }

        if(index <= list.children.length-1 && !row.querySelector('.btn-down-player')) {
            list.children[list.children.length-1].querySelector('.btn-down-player').remove();
            const actions = row.querySelector('.actions');
            actions.insertBefore(this._createDownButton(), actions.children[actions.children.length-1]);
        }
    }

    _moveElementDown(element)
    {
        const row = element.closest('div.row');
        const list = document.getElementById('selectedUserList');
        const index = this._listIndexOf(list, row);
        this._moveListElement(list, index, index+2);

        if(index+2 >= list.children.length-1 && (list.children.length > 1)) {
            row.querySelector('.btn-down-player').remove();
            const actions = list.children[list.children.length-2].querySelector('.actions');
            actions.insertBefore(this._createDownButton(), actions.children[actions.children.length-1]);
        }

        if(index+2 >= 0 && !row.querySelector('.btn-up-player')) {
            list.children[0].querySelector('.btn-up-player').remove();
            const actions = row.querySelector('.actions');
            actions.insertBefore(this._createUpButton(), actions.children[actions.children.length]);
        }
    }

    _moveListElement(list, from, to = null)
    {
        const c = list.children;
        const element = c[from];
        if(to === null) {
            list.appendChild(element)
        } else {
            list.insertBefore(element, c[to]);
        }
    }

    _listIndexOf(list, element)
    {
        const nodes = Array.prototype.slice.call( list.children );
        return nodes.indexOf(element);
    }

    _animateCSS(element, animation, duration = 1, prefix = 'animate__')
    {
        return new Promise((resolve, reject) =>
        {
            const animationName = `${prefix}${animation}`;
            const node = element;

            node.classList.add(`${prefix}animated`, animationName);
            node.style.setProperty('--animate-duration', `${duration}s`);

            function handleAnimationEnd(event)
            {
                event.stopPropagation();
                node.classList.remove(`${prefix}animated`, animationName);
                node.remove();
                resolve('Animation ended');
            }

            node.addEventListener('animationend', handleAnimationEnd, {once: true});
        });
    }
}
