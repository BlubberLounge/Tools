import Game from "./game";
import GameType from "../../enums/gameType";

/**
 *
 *
 *
 */
export default class GameCricket extends Game
{

    constructor()
    {
        super();
        if(this.type != GameType.cricket)
            console.error('Something is wrong. Please check the gametype!');

        console.info('Cricket Game variant.');
    }

    run()
    {

    }

}
