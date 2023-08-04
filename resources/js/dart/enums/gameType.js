
/**
 *
 *
 *
 */
export default class GameType
{
    static X01 = new GameType('X01');
    static aroundTheClock = new GameType('aroundTheClock');
    static cricket = new GameType('cricket');
    static unkown = new GameType('unkown');

    constructor(name)
    {
        this.name = name;
    }

    toString()
    {
        return this.name;
    }

    static fromString(string)
    {
        if(string === 'X01') {
            return GameType.X01;
        } else if(string === 'aroundTheClock') {
            return GameType.aroundTheClock;
        } else if(string === 'cricket') {
            return GameType.cricket;
        } else {
            console.error('Unkown GameType: ' + string);
            return GameType.unkown;
        }
    }

}
