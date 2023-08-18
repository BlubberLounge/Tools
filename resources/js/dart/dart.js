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

        // console.log('Dart initializing');

        this.dartboard = new Dartboard(this.boardContainer, {size: 360});
        this.dartboard.render();
        this.playerList.lock();
        this.playerList.sortByPosition();

        // https://developer.mozilla.org/en-US/docs/Web/API/Window/sessionStorage?retiredLocale=de#saving_text_between_refreshes

        let t = document.getElementById('gameType').getAttribute('value');
        if(t == 'X01') {
            this.game = new GameX01();
        } else if(t == 'aroundTheClock') {
            // this.game = new GameAroundTheClock();
            this.game = null;
        } else if(t == 'cricket') {
            // this.game = new GameCricket();
        }

        // needs better error handling
        if(!this.game instanceof Game)
            console.error('GameType is unkown.');

        this.game.dartboardSize = this.dartboard.board.width;
        // this.game.run();

        this.addListeners();

        this.initialized = true;
    }

    addListeners()
    {
        document.querySelector(this.boardContainer).addEventListener('dartHit', h =>
        {
            const hit = h.detail;
            this._placeHitMarker(hit);
            this.game.addThrow(hit.points, hit.field, hit.ring, hit.x, hit.y);
            this.game.run();
        });

        document.addEventListener('dartClearBoard', h =>
        {
            this._clearHitMarker();
        });
    }

    _placeHitMarker(hit)
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

        // console.log('hit placed');

        document.getElementsByClassName('c-Dartboard')[0].append(hitMarker);

        return hitMarker;
    }

    _clearHitMarker()
    {
        let hitMarkers = document.querySelectorAll('.hitMarker');
        // console.log(hitMarkers);

        hitMarkers.forEach( marker => marker.remove() );
    }
}
