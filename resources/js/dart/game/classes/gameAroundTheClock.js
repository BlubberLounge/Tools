import Game from "./game";
import GameType from "../../enums/gameType";

/**
 *
 *
 *
 */
export default class GameAroundTheClock extends Game
{

    constructor()
    {
        super();
        if(this.type != GameType.aroundTheClock)
            console.error('Something is wrong. Please check the gametype!');

        console.info('Around-The-Clock Game variant.');
    }

    run()
    {

    }

}
