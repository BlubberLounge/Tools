import Player from './player';

/**
 *
 */
export default class PlayerList
{
    players = [];

    constructor(players = null)
    {
        this.isLocked = false;
        this.pointer = -1;

        if(players)
            this.addBatch(players);
    }

    add(player)
    {
        if(this.isLocked)
            return;

        player.pos = this.players.length;

        if(player instanceof Player) {
            this.players.push(player);
        } else {
            let fullName = player.firstname +' '+ player.lastname;
            this.players.push(new Player(player.id, player.name, fullName, player.pos));
        }

        console.log('Player Added to the List');

        return this.players[this.players.length > 0 ? this.players.length-1 : 0];
    }

    addBatch(players)
    {
        if(this.isLocked)
            return;

        players.forEach(player =>
        {
            this.add(player);
        });
    }

    remove(user)
    {
        if(this.isLocked)
            return;

        this.players = this.players.filter( player => player.id !== user.id );
        return this;
    }

    getFirst()
    {
        this.pointer = 0;
        return this._getPlayer();
    }

    getLast()
    {
        this.pointer = this.count() - 1;
        return this._getPlayer();
    }

    next(skip = 0)
    {
        this._incrementPointerRollover(skip+1);
        return this._getPlayer();
    }

    nextNonWinner()
    {
        for(let i = this.pointer; i <= 10; i++) {
            // console.log('\n');
            // console.log(this.pointer);
            // console.log(this._getPlayer());
            // console.log('\n');

            if(!this.next().hasWon()) {
                // console.log('\n');
                // console.log(this.pointer);
                // console.log(this._getPlayer());
                // console.log('\n');
                return this._getPlayer();
            }
        }
    }

    previous(skip = 0)
    {
        this._decrementPointerRollover(skip+1);
        return this._getPlayer();
    }

    count()
    {
        return this.players.length;
    }

    sortByPosition()
    {
        this.players?.sort((a, b) => (a.pos > b.pos ? 1 : -1))
    }

    getWinner()
    {
        return this.players.filter( player => player.hasWon() == true );
    }

    getNonWinner()
    {
        return this.players.filter( player => player.hasWon() == false );
    }

    moveUp(user)
    {
        this.sortByPosition();
        let playerIndex = this._findIndexOfPlayer(user);

        console.log(this.players);
        this.players.splice(playerIndex-1, 0, arr.splice(playerIndex, 1)[0]);
        this.pointer = playerIndex-1;
        console.log(this.players);

        return this._getPlayer();
    }

    array_move(arr, old_index, new_index) {
        if (new_index >= arr.length) {
            var k = new_index - arr.length + 1;
            while (k--) {
                arr.push(undefined);
            }
        }
        arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
        return arr; // for testing
    };

    lock()
    {
        this.isLocked = true;
    }

    unlock()
    {
        this.isLocked = false;
    }

    _findIndexOfPlayer(user)
    {
        return this.players.findIndex( u => u.id === user.id );
    }

    _incrementPointer(incrementBy = 1)
    {
        this.pointer = this.pointer+incrementBy > this.count()-1 ? this.count()-1 : this.pointer+incrementBy
        return this.pointer;
    }

    _incrementPointerRollover(incrementBy = 1)
    {
        this.pointer = this.pointer+incrementBy > this.count()-1 ? 0 : this.pointer+incrementBy;
        return this.pointer;
    }

    _decrementPointer(decrementBy = 1)
    {
        this.pointer = this.pointer-decrementBy < 0 ? 0 : this.pointer-decrementBy;
        return this.pointer;
    }

    _decrementPointerRollover(decrementBy = 1)
    {
        this.pointer = this.pointer-decrementBy < 0 ? this.count()-1 : this.pointer-decrementBy;
        return this.pointer;
    }

    _getPlayer()
    {
        return this.players[this.pointer];
    }
}
