/**
 * @author Maximilian Mewes
 *
 *
 */

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import PlayerList from './playerList';
import Game from "./game";
import GameX01 from "./gameX01";

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


        let t = document.getElementById('gameType').getAttribute('value');
        if(t == 'X01') {
            this.game = new GameX01();
        } else if(t == 'aroundTheClock') {
            // this.game = new GameAroundTheClock();
        } else if(t == 'cricket') {
            // this.game = new GameCricket();
        }

        if(!this.game instanceof Game)
            console.error('GameType is unkown.');


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
        let hitMarker = document.createElement("i");
        // let hitMarker = document.createElement('img');
        // hitMarker.setAttribute('src', '/img/dart-arrow.svg');
        hitMarker.classList.add('hitMarker');
        // hitMarker.classList.add('fa-solid', 'fa-location-pin');

        let topPosition = hit.y - (8/2); // center hitMarker on cursor tip
        let leftPosition = hit.x - (8/2); // center hitMarker on cursor tip

        hitMarker.style.setProperty("top", topPosition+"px");
        hitMarker.style.setProperty("left", leftPosition+"px");
        // console.log(UTILS.cartesian2Polar(hit.x-200, hit.y-200));

        document.getElementById('dartboard').append(hitMarker);

        return hitMarker;
    }
}
