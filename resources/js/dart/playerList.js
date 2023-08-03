import Player from './player';
import PlayerFactory from './playerFactory';

/**
 *
 */
export default class PlayerList
{
    players = [];

    constructor(players = null)
    {
        this.playerFactory = new PlayerFactory();
        this.isLocked = false;

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

        // await this.playerFactory.createByID(id).then(r =>
        // {
        //     if(r instanceof Player) {
        //         r.position = this.players.length;
        //         this.players.push(r);
        //         console.log('Player Added to the List');
        //     }
        // });

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
