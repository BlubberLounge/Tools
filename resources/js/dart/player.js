import Throw from "./throw";

/**
 *
 */
export default class Player
{

    constructor(id, name, fullName, position = 0)
    {
        this.id = id;
        this.name = name;
        this.fullName = fullName;
        this.position = position;

        this.throws = [];
    }

    addThrow(set, leg, turn, x, y, radius)
    {
        let newThrow = new Throw(set, leg, turn, x, y, radius);
        this.throws.push(
            newThrow
        );

        console.log('New Throw added:');
        console.log(newThrow);
    }

}
