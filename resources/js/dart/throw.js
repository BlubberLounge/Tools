/**
 *
 *
 *
 */
export default class Throw
{
    constructor(set, leg, turn, throwNum, value, field, ring, x, y, radius)
    {
        this.set = set;
        this.leg = leg;
        this.turn = turn;
        this.throwNum = throwNum;
        this.value = value;
        this.field = field;
        this.ring = ring;
        this.x = x - radius;
        this.y = (y - radius) * -1; // JS Fuckery idk why only Y flips to a negative value
        this.radius = radius; // only x_origin = y_origin, since a dartboard is always round

        this.id = this._getId();

        this.hasBeenSaved = false;

        this.x_normalized = this._normalize(this.x);
        this.y_normalized = this._normalize(this.y);
    }

    /**
     * Rember: this returns raw polar coordinates. see factories for more information
     *
     * @param {boolean} normalized
     * @returns
     */
    getPolarCoordinates(normalized = false)
    {
        const x = normalized ? this.x_normalized : this.x;
        const y = normalized ? this.y_normalized : this.y;

        const rad2deg = (rad) => rad * (180/Math.PI);

        const radius = Math.sqrt(x*x + y*y);
        const theta = Math.atan2(y,x);

        return {
            radius: radius,
            theta: theta,
            degrees: rad2deg(theta)
        };
    }

    /**
     *
     *
     * @param {boolean} normalized
     * @returns
     */
    getCartesianCoordinates(normalized = false)
    {
        const x = normalized ? this.x_normalized : this.x;
        const y = normalized ? this.y_normalized : this.y;

        return {
            x: x,
            y: y,
        };
    }

    /**
     *
     */
    saved()
    {
        this.hasBeenSaved = true;
    }

    /**
     *
     * @param {int} val x or y
     * @returns
     */
    _normalize(val)
    {
        return val / this.radius;
    }

    /**
     *
     *
     */
    _getId()
    {
        return Throw.makeId(this.set, this.leg, this.turn, this.throwNum);
    }

    static makeId(set, leg, turn, throwNum)
    {
        let idParts = [
            set,
            leg,
            turn,
            throwNum,
        ];

        return idParts.join('-');
    }
}
