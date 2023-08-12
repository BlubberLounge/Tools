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

        this.singleOut = true;
        this.doubleOut = true;
        this.trippleOut = true;
        this.singleIn = true;
        this.doubleIn = true;
        this.trippleIn = true;

        this.currentSet = 1;
        this.currentLeg = 1;
        this.currentTurn = 1;
        this.currentThrow = 0;
        this.currentPlayer = null;

        this.winCounter = 1;

        this.dartboardSize = null;

        this.settings = new GameSettings();

        this._init();
    }

    nextTurn(reset = false)
    {
        this._saveTurn();
        this.currentTurn = reset ? 0 : this.currentTurn + 1;
        this._dispatchEvent('dartClearBoard', []);
        this.nextPlayer();

        console.log('Next Turn');
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
        this.currentPlayer = this.users.nextNonWinner();

        if(this.users.getNonWinner().length <= 1)
            console.log();

        this._dispatchEvent('dartClearBoard', []);
        this.currentThrow = 0;
    }

    currentPlayerWon()
    {
        console.warn('Player won!');
        this.currentPlayer.setWin(this.winCounter);
        this._savePlayerWon();
        this.winCounter++;
        if(this.winCounter >= this.users.count()) {
            console.log('Game done. every one got a place');
            this._saveTurn();
            if(this.users.getNonWinner().length >= 1) {
                this._nextPlayer();
                this._savePlayerWon();
            }

            this._done();   // bailout / exit
        } else {
            if(this.detectNextTurn(false)) {
                this._nextTurn();
            } else {
                this._nextPlayer();
            }
        }
    }

    currentPlayerResetTurn()
    {
        // API call to soft delete throws
        // console.warn('The last Turn of the player to removed');
        this.currentPlayer.removeThrowsByTurn(this.currentSet, this.currentLeg, this.currentTurn);

        if(this.detectNextTurn(false)) {
            this._nextTurn();
        } else {
            this._nextPlayer();
        }
    }

    addThrow(points, fieldName, ringName, x, y)
    {
        this.currentPlayer.addThrow(this.currentSet, this.currentLeg, this.currentTurn, points, fieldName, ringName, x, y, this.dartboardSize/2);
        this.currentThrow++;
    }

    detectNextPlayer()
    {
        // return this.currentPlayer.getNextThrowNumber(this.currentSet, this.currentLeg, this.currentTurn) > this.settings.maxThrowsPerTurn;
        return this.currentThrow+1 > this.settings.maxThrowsPerTurn;
    }

    detectNextTurn(detectNextPlayer = true)
    {
        if(detectNextPlayer) {
            return ((this.currentPlayer.pos+1 > this.users.count()-1) || (this.winCounter >= this.users.count())) && this.detectNextPlayer();
        } else {
            return (this.currentPlayer.pos+1 > this.users.count()-1) || (this.winCounter >= this.users.count());
        }
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
        console.log('Saving Turn.');

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
                    field: wurf.field == 'B' || wurf.ring == 'DB' ? 25 : wurf.field,
                    ring: wurf.ring == 'B' ? 'S' : (wurf.ring == 'DB' ? 'D' : wurf.ring),
                    x: wurf.x_normalized,
                    y: wurf.y_normalized,
                    valid: wurf.valid,
                }

                data.push(ThrowData);
            });
        });

        let result = await axios.post('/api/v1/throw', data).then( response => {
            this._dispatchEvent('dartTurnSaved', response.data.data);

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
        this.users.sortByPosition();
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

        this.currentPlayer = this.users.getFirst();

        document.addEventListener('dartTurnSaved', h =>
        {
            let data = h.detail;
            this.users.players.forEach( player => player.setThrowsByTurnSaved(data.set, data.leg, data.turn));
        });
    }


    _done()
    {
        this._changeStatus('DONE');
        location.reload();
    }

    _savePlayerWon()
    {
        var result = $.ajax({
            url: this.settings.apiBasePath+'/dart/updatePlace/'+this.id+'/'+this.currentPlayer.id,
            type: 'PUT',
            async: false,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            data: {
                place: this.winCounter,
            },
            success: function (data)
            {
                return data.data;
            },
            error: function (xhr, exception) {
                console.error(xhr);
                return null;
            }
        });
        // console.log(result);
    }

    _changeStatus(status = null)
    {
        if(status)
            var result = $.ajax({
                url: this.settings.apiBasePath+'/dart/'+this.id,
                type: 'PUT',
                async: false,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                data: {
                    status: status,
                },
                success: function (data)
                {
                    return data.data;
                },
                error: function (xhr, exception) {
                    console.error(xhr);
                    return null;
                }
            });
        // console.log(result);
    }

    _fetchDetails()
    {
        // let result = await axios({
        //     method: 'get',
        //     url: '/api/v1/dart/'+this.id,
        // }).data;

        let result = $.ajax({
            url: this.settings.apiBasePath+'/dart/'+this.id,
            type: 'GET',
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

    _animateCounter(obj, start, end, duration)
    {
        let diff = Math.abs(start - end);
        duration = (500 + 60 * 2) - (diff * 2);

        let startTimestamp = null;
        const step = (timestamp) =>
        {
          if (!startTimestamp) startTimestamp = timestamp;

          const progress = Math.min((timestamp - startTimestamp) / duration, 1);
          obj.innerHTML = Math.floor(progress * (end - start) + start);

          if (progress < 1)
            window.requestAnimationFrame(step);
        };

        window.requestAnimationFrame(step);
    }

    _dispatchEvent(eventName, content)
    {
        document.dispatchEvent(new CustomEvent(eventName, {
            detail: content,
        }));
    }
}
