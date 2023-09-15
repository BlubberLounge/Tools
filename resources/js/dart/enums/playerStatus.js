
/**
 *
 *
 *
 */
export default class PlayerStatus
{
    static pending = new PlayerStatus('pending');
    static accepted = new PlayerStatus('accepted');
    static denied = new PlayerStatus('denied');

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
        if(string === 'pending') {
            return PlayerStatus.pending;
        } else if(string === 'accepted') {
            return PlayerStatus.accepted;
        } else if(string === 'denied') {
            return PlayerStatus.denied;
        } else {
            console.error('Unkown PlayerStatus: ' + string);
            return PlayerStatus.pending;
        }
    }

}
