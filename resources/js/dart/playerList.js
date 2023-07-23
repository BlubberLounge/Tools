import Player from './player';
import PlayerFactory from './playerFactory';

/**
 *
 */
export default class PlayerList
{
    players = [];

    constructor()
    {
        this.playerFactory = new PlayerFactory();
        this.isLocked = false;
    }

    add(id)
    {
        if(this.isLocked)
            return;

        this.playerFactory.createByID(id).then(r =>
        {
            if(r instanceof Player) {
                r.position = this.players.length;
                this.players.push(r);
            }
        });

        console.log('Player Added to the List');

        return this;
    }

    remove(id)
    {
        if(this.isLocked)
            return;

        this.players = this.players.filter( player => player.id !== id );
        return this;
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
