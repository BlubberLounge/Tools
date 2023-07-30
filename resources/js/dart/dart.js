/**
 * @author Maximilian Mewes
 *
 *
 */

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import PlayerList from './playerList';

/**
 * Singleton Dart entry class
 *
 */
export default class Dart
{
    constructor(boardContainer = null)
    {
        if(Dart.instance)
            return Dart.instance;

        Dart.instance = this;

        this.boardContainer = boardContainer;
        this.playerList = new PlayerList();

        this.initialized = false;
    }

    init()
    {
        if(this.initialized)
            return;

        console.log('Dart initializing');

        this.dartboard = new Dartboard(this.boardContainer);
        this.dartboard.render();
        this.playerList.lock();
        this.playerList.sortByPosition();

        this.addListeners();

        this.initialized = true;
    }

    addListeners()
    {
        document.querySelector(this.boardContainer).addEventListener('dartHit', h =>
        {
            const hit = h.detail;
            this.placeHitMarker(hit);
        });
    }

    placeHitMarker(hit)
    {
        console.log(hit);
        console.log(this.playerList.players);
        let hitMarker = document.createElement("i");
        hitMarker.classList.add('hitMarker');

        let topPosition = hit.y - (8/2); // center hitMarker on cursor tip
        let leftPosition = hit.x - (8/2); // center hitMarker on cursor tip

        hitMarker.style.setProperty("top", topPosition+"px");
        hitMarker.style.setProperty("left", leftPosition+"px");
        // console.log(UTILS.cartesian2Polar(hit.x-200, hit.y-200));

        document.getElementById('dartboard').append(hitMarker);

        return hitMarker;
    }
}
