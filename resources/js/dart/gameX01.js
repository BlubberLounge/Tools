import Game from "./game";
import GameType from "./enums/gameType";
import Throw from "./throw";

/**
 *
 *
 *
 */
export default class GameX01 extends Game
{

    constructor()
    {
        super();
        if(this.type != GameType.X01)
            console.error('Something is wrong. Please check the gametype!');

        console.info('X01 Game variant.');
        // console.log(this.users);

        // this.throw = new Throw(1, 360, 180, 360/2);
        // console.log(this.throw.getCartesianCoordinates(true));

        // console.log(this.users.next());
    }

    run()
    {
        let isNextTurn = this.detectNextTurn();

        if(isNextTurn) {
            console.log('Next Turn');
            this._deselectPlayer();
            this.nextTurn();
            this._selectPlayer();
        }

        if(!isNextTurn)
            if(this.detectNextPlayer()) {
                console.log('Next Player');
                this._deselectPlayer();
                this.nextPlayer();
                this._selectPlayer();
            }
    }

    addThrow(points, fieldName, ringName, x, y)
    {
        super.addThrow(points, fieldName, ringName, x, y);
        this.run();
    }

    _selectPlayer()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        playerCard.classList.add('text-bg-secondary');
    }

    _deselectPlayer()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        playerCard.classList.remove('text-bg-secondary');
    }
}
