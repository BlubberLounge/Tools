/**
 * @author Maximilian Mewes
 *
 *
 */

import Dartboard from './dartboard';


class Dart
{
    constructor(boardContainer = null)
    {
        this.boardContainer = boardContainer;
    }

    init()
    {
        console.log('Dart initializing');
        this.dartboard = new Dartboard(this.boardContainer);
        this.dartboard.render();
    }
}

export {
    Dart as default,
}
