import Battery from "./battery"

/**
 * represents a Lithium-Ion
 * 
 */
export default class LiOn extends Battery
{
    constructor(maxVoltage, minVoltage, capacity, level)
    {
        super(maxVoltage, minVoltage, capacity, level);
        this.type = 'LiOn';
        console.log("Battery "+ this.type +" loaded.");
    }

    // TODO add battery type specific limits
}