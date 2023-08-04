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
            this.nextTurn();
        }

        if(!isNextTurn)
            if(this.detectNextPlayer()) {
                console.log('Next Player');
                this.nextPlayer();
            }

    }

    test()
    {
        console.log(document.querySelectorAll('[data-user-id]'));
        this.addThrow(60, 'T20', 'triple', 177, 95.4375);
        this.addThrow(60, 'T20', 'triple', 200, 95.4375);
        console.log(this.currentPlayer);
        this.nextPlayer();
        this.addThrow(50, 'DB', 'bull', 180, 180);
        this.addThrow(50, 'DB', 'bull', 180, 180);
        console.log(this.currentPlayer);
        console.log('==========================');
        this.nextTurn();

        this.addThrow(60, 'T20', 'triple', 177, 95.4375);
        this.addThrow(60, 'T20', 'triple', 200, 95.4375);
        console.log(this.currentPlayer);
        this.nextPlayer();
        this.addThrow(50, 'DB', 'bull', 180, 180);
        this.addThrow(50, 'DB', 'bull', 180, 180);
        console.log(this.currentPlayer);
    }

    addThrow(points, fieldName, ringName, x, y)
    {
        super.addThrow(points, fieldName, ringName, x, y);
        this.run();
    }


    _buildUserInformation()
    {

    }
}
