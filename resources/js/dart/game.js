import { thresholdFreedmanDiaconis } from "d3";
import GameType from "./enums/gameType";
import GameSettings from "./gameSettings";
import PlayerList from "./playerList";

/**
 *
 *
 *
 */
export default class Game
{
    constructor()
    {
        this.id = null;
        this.users = null;
        this.createdBy = null;
        this.type = null;
        this.status = null;
        this.private = false;

        this.title = null;
        this.comment = null;

        this.points = undefined;
        this.start = undefined;
        this.end = undefined;
        this.fields = undefined;

        this.singleOut = true;
        this.doubleOut = true;
        this.trippleOut = true;
        this.singleIn = true;
        this.doubleIn = true;
        this.trippleIn = true;

        this.currentSet = 1;
        this.currentLeg = 1;
        this.currentTurn = 1;
        this.currentPlayer = null;

        this.dartboardSize = null;

        this.settings = new GameSettings();

        this._init();
    }

    nextTurn(reset = false)
    {
        this._saveTurn();
        this.currentTurn = reset ? 0 : this.currentTurn + 1;
        this.currentPlayer = this.users.getFirst();
        this._dispatchEvent('dartClearBoard', []);
    }

    nextLeg(reset = false)
    {
        this.nextTurn(true)
        this.currentLeg = reset ? 0 : this.currentLeg + 1;
    }

    nextSet()
    {
        this.nextLeg(true)
        this.currentSet++;
    }

    nextPlayer()
    {
        this.currentPlayer = this.users.next();
        this._dispatchEvent('dartClearBoard', []);
    }

    addThrow(points, fieldName, ringName, x, y)
    {
        this.currentPlayer.addThrow(this.currentSet, this.currentLeg, this.currentTurn, points, fieldName, ringName, x, y, this.dartboardSize/2);
    }

    detectNextPlayer()
    {
        return this.currentPlayer.getNextThrowNumber(this.currentSet, this.currentLeg, this.currentTurn) > this.settings.maxThrowsPerTurn;
    }

    detectNextTurn()
    {
        return (this.currentPlayer.pos+1 > this.users.count()-1) && this.detectNextPlayer();
    }

    detectNextLeg()
    {
        return false;
    }

    detectNextSet()
    {
        return false;
    }

    async _saveTurn()
    {
        // send to api and or local session storage
        let data = [];

        this.users.players.forEach( user => {
            user.getThrowsByTurn(this.currentSet, this.currentLeg, this.currentTurn).forEach( wurf => {
                let ThrowData = {
                    game: this.id,
                    user: user.id,
                    set: wurf.set,
                    leg: wurf.leg,
                    turn: wurf.turn,
                    throw: wurf.throwNum,
                    value: wurf.value,
                    // field: wurf.field,
                    // ring: wurf.ring,
                    x: wurf.x_normalized,
                    y: wurf.y_normalized,
                    data_input_type: 'DARTBOARD',
                }

                data.push(ThrowData);
            });
        });

        let result = await axios.post('/api/v1/throw', data).then( response => {
            let data = response.data.data;
            this._dispatchEvent('dartTurnSaved', {
                set: data.currentSet,
                leg: data.currentLeg,
                turn: data.currentTurn,
            });
        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
                console.error('Throws could not get saved!');
            }
        });
    }

    _init()
    {
        console.info('<========== Initalising DartGame ==========>');
        this.id = document.getElementById('gameId').getAttribute('value');

        let details = this._fetchDetails().game;

        this.users = new PlayerList(details.users);
        this.users.lock();
        console.log(this.users);

        this.createdBy = details.created_by.firstname +' '+ details.created_by.lastname;
        this.type = GameType.fromString(details.type);
        this.status = details.status;
        this.private = details.private;

        this.title = details.title;
        this.comment = details.comment;

        if(details.points)
            this.points = details.points;

        if(details.start)
            this.start = details.start;

        if(details.end)
            this.end = details.end;

        if(details.fields)
            this.fields = details.fields;

        this.singleOut = details.singleOut;
        this.doubleOut = details.doubleOut;
        this.trippleOut = details.trippleOut;
        this.singleIn = details.singleIn;
        this.doubleIn = details.doubleIn;
        this.trippleIn = details.trippleIn;

        this.nextPlayer();

        document.addEventListener('dartTurnSaved', h =>
        {
            let data = h.detail;
            // console.log(data);

            this.users.getFirst().setThrowsByTurnSaved(data.set, data.leg, data.turn);
            // console.log(this.users.getFirst().getThrowsByTurn(data.set, data.leg, data.turn));
        });
    }

    _fetchDetails()
    {
        // let result = await axios({
        //     method: 'get',
        //     url: '/api/v1/dart/'+this.id,
        // }).data;

        let result = $.ajax({
            url: '/api/v1/dart/'+this.id,
            type: "GET",
            async: false,
            success: function (data)
            {
                return data.data;
            },
            error: function (xhr, exception) {
                var msg = "";
                if (xhr.status === 0) {
                    msg = "Not connect.\n Verify Network." + xhr.responseText;
                } else if (xhr.status == 404) {
                    msg = "Requested page not found. [404]" + xhr.responseText;
                } else if (xhr.status == 500) {
                    msg = "Internal Server Error [500]." +  xhr.responseText;
                } else if (exception === "parsererror") {
                    msg = "Requested JSON parse failed.";
                } else if (exception === "timeout") {
                    msg = "Time out error." + xhr.responseText;
                } else if (exception === "abort") {
                    msg = "Ajax request aborted.";
                } else {
                    msg = "Error:" + xhr.status + " " + xhr.responseText;
                }

                console.error(msg);
                return null;
            }
        });

        return result.responseJSON.data;
    }

    _dispatchEvent(eventName, content)
    {
        document.dispatchEvent(new CustomEvent(eventName, {
            detail: content,
        }));
    }
}
