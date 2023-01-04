import Battery from "./battery"

/**
 * represents a Lithium-Ion / Lithium-Polymer
 * 
 */
export default class LiPo extends Battery
{
    constructor(maxVoltage, minVoltage, capacity, level)
    {
        super(maxVoltage, minVoltage, capacity, level);
        this.type = 'LiPo';
        console.log("Battery "+ this.type +" loaded.");
    }

    // TODO add battery type specific limits
}