import Throw from "./throw";

/**
 *
 */
export default class Player
{

    constructor(id, name, fullName, pos = 0)
    {
        this.id = id;
        this.name = name;
        this.fullName = fullName;
        this.pos = pos;
        this.winningPosition = null;

        this.throws = [];
    }

    addThrow(set, leg, turn, value, field, ring, x, y, radius)
    {
        let throwNum = this.getNextThrowNumber(set, leg, turn);

        let newThrow = new Throw(set, leg, turn, throwNum, value, field, ring, x, y, radius);
        this.throws.push(
            newThrow
        );

        // console.log('New Throw added:');
        // console.log(newThrow);
        // console.log('Searched Throw:');
        // console.log(this.getThrow(set, leg, turn, throwNum));
        // console.log('ThrowsByTurn:');
        // console.log(this.getThrowsByTurn(set, leg, turn));
    }

    getTotalPoints()
    {
        let totalPoints = 0;
        this.throws.forEach(wurf => {
            totalPoints += wurf.value;
        });

        return totalPoints;
    }

    getNextThrowNumber(set, leg, turn)
    {
        let newThrowNumber = 1;
        let lastThrow = this.throws.length > 0 ? this.getLastThrow() : {set: -1, leg: -1, turn: -1};

        if(set == lastThrow.set && leg == lastThrow.leg && turn == lastThrow.turn) {
            newThrowNumber = lastThrow.throwNum + 1;
        }

        return newThrowNumber;
    }

    getLastThrow()
    {
        return this.throws[this.throws.length - 1];
    }

    getThrow(set, leg, turn, throwNum)
    {
        let id = Throw.makeId(set, leg, turn, throwNum);
        return this.throws.filter(wurf => wurf.id === id)[0];
    }

    getThrowsByTurn(set, leg, turn)
    {
        return this.throws.filter(wurf => wurf.set == set && wurf.leg == leg && wurf.turn == turn);
    }

    getThrowsByLeg(set, leg)
    {
        return this.throws.filter(wurf => wurf.set == set && wurf.leg == leg);
    }

    getThrowsBySet(set)
    {
        return this.throws.filter(wurf => wurf.set == set);
    }

    getTotalThrowCount()
    {
        return this.throws.length;
    }

    getAverage()
    {
        let sum = this.throws.reduce((total, t) => total + t.value, 0);
        // console.log(sum);
        return sum / this.getTotalThrowCount();
    }

    setThrowsByTurnSaved(set, leg, turn)
    {
        this.getThrowsByTurn(set, leg, turn).map( t => t.saved());
        // this.getThrow(set, leg, turn, 1).saved();
    }

    removeThrowsByTurn(set, leg, turn)
    {
        this.throws = this.throws = this.throws.filter(wurf => wurf.set == set && wurf.leg == leg && wurf.turn != turn);
    }

    setWin(position)
    {
        this.winningPosition = position;
    }

    hasWon()
    {
        return this.winningPosition != null;
    }
}
