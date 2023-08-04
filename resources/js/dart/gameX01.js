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
        console.log(document.querySelectorAll('[data-user-id]'));
        this.currentPlayer.addThrow(this.currentSet, this.currentLeg, this.currentTurn, this.dartboardSize/2, this.dartboardSize/2, this.dartboardSize/2);

        console.log(this.currentPlayer);
    }

    _buildUserInformation()
    {

    }
}
