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

        player.position = this.players.length;

        if(player instanceof Player) {
            this.players.push(player);
        } else {
            let fullName = player.firstname +' '+ player.lastname;
            this.players.push(new Player(player.id, player.name, fullName, player.position));
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

    next()
    {
        this._incrementPointer();
        return this.players[this.pointer];
    }

    previous()
    {
        this._decrementPointer();
        return this.players[this.pointer];
    }

    _incrementPointer()
    {
        return this.pointer++;
    }

    _decrementPointer()
    {
        return this.pointer--;
    }

    sortByPosition()
    {
        this.players?.sort((a, b) => (a.position > b.position ? 1 : -1))
    }

    lock()
    {
        this.isLocked = true;
    }

    unlock()
    {
        this.isLocked = false;
    }
}
