import Game from "./game";
import GameType from "../../enums/gameType";
import DartDefinition from "../../dartDefinition";

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
    }

    async run()
    {
        await this._show();

        if (this._remainingPlayerPoints() == 0) {
            this._currentPlayerWon();

        } else if(this._remainingPlayerPoints() < 0) {
            super.currentPlayerResetTurn();

        } else {
            if(this.detectNextTurn()) {
                this._nextTurn();
            } else if(this.detectNextPlayer()) {
                this._nextPlayer();
            }
        }

    }

    addThrow(points, fieldName, ringName, x, y)
    {
        super.addThrow(points, fieldName, ringName, x, y);
    }

    async _show()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        let throwDisplays = [];
        for(let i = 1; i <= DartDefinition.maxThrowsPerTurn; i++) {
            throwDisplays.push(playerCard.querySelector('.throw-'+i));
        }

        let lastThrow = this.currentPlayer.getLastThrow();
        if(!lastThrow)
            return;

        let throwDisplay = playerCard.querySelector('.throw-'+lastThrow.throwNum);
        throwDisplay.innerHTML = lastThrow.value;

        let totalDisplay = playerCard.querySelector('.total');
        await super._animateCounter(totalDisplay, parseInt(totalDisplay.innerHTML), this._remainingPlayerPoints(), 400);

        if(lastThrow.throwNum == DartDefinition.maxThrowsPerTurn) {
            let total = 0;
            let turnTotalDisplay = playerCard.querySelector('.turn-total');

            this.currentPlayer.getThrowsByTurn(this.currentSet, this.currentLeg, this.currentTurn).forEach(wurf => {
                total += wurf.value;
            });

            turnTotalDisplay.innerHTML = total;
        }

        // await new Promise(r => setTimeout(r, 1000));

        // clear display
        let isNextTurn = this.detectNextTurn();
        if(isNextTurn)
            this._clearDisplay();

        let isNextUser = this.detectNextPlayer();
        if(isNextUser) {
            let avgDisplay = playerCard.querySelector('.avg');
            avgDisplay.innerHTML = (this.currentPlayer.getAverage()).toFixed(2);
        }
    }

    _currentPlayerWon()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        playerCard.classList.remove('text-bg-secondary');
        playerCard.classList.add('text-bg-success');

        let cardBody = playerCard.querySelector('.playercard-body');
        cardBody.innerHTML = '';

        let row = () => {
            let el = document.createElement('div');
            el.classList.add('row', 'justify-center');
            return el;
        };

        let col = (auto = false) => {
            let el = document.createElement('div');
            el.classList.add(auto ? 'col-auto' : 'col' , 'd-flex', 'justify-center');
            return el;
        };

        let topRow = row();
        let topCol = col(true);
        let h4 = document.createElement('h4');
        h4.innerHTML = `${this.currentPlayer.fullName}`;
        topCol.appendChild(h4);
        topRow.appendChild(topCol);

        let middleRow = row();
        let mLeftCol = col();
        let mRightCol = col();
        let aText = document.createElement('p');
        aText.innerHTML = 'used Throws: '+ this.currentPlayer.getTotalThrowCount();
        let bText = document.createElement('p');
        bText.innerHTML = 'Average: '+ this.currentPlayer.getAverage().toFixed(2);
        mLeftCol.appendChild(aText);
        mRightCol.appendChild(bText);
        middleRow.appendChild(mRightCol);
        middleRow.appendChild(mLeftCol);

        let bottomRow = row();
        let bLeftCol = col(true);
        bLeftCol.innerHTML = 'Platz '+ this.winCounter;
        bottomRow.appendChild(bLeftCol);

        cardBody.appendChild(topRow);
        cardBody.appendChild(middleRow);
        cardBody.appendChild(bottomRow);

        super.currentPlayerWon();
    }

    _clearDisplay()
    {
        console.log('cleared');
        this.users.getNonWinner().forEach(user => {
            let playerCard = document.querySelector(`[data-user-id='${user.id}']`);

            let totalDisplay = playerCard.querySelector('.total');
            totalDisplay.innerHTML = this.points - user.getTotalPoints();
            console.log(totalDisplay.innerHTML);

            for(let i = 1; i <= DartDefinition.maxThrowsPerTurn; i++) {
                playerCard.querySelector('.throw-'+i).innerHTML = '<i class="fa-solid fa-xmark text-danger"></i>';
            }
            playerCard.querySelector('.turn-total').innerHTML = 0;
        });
    }

    _nextPlayer()
    {
        this._deselectPlayer();
        super.nextPlayer();
        this._selectPlayer();
    }

    _nextTurn()
    {
        this._clearDisplay();
        this._deselectPlayer();
        super.nextTurn();
        this._selectPlayer();
    }

    _selectPlayer()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        playerCard.classList.add('text-bg-secondary');
    }

    _deselectPlayer()
    {
        let playerCard = document.querySelector(`[data-user-id='${this.currentPlayer.id}']`);
        playerCard.classList.remove('text-bg-secondary');
    }

    _remainingPlayerPoints()
    {
        return this.points - this.currentPlayer.getTotalPoints();
    }
}
