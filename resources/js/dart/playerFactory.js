import Player from './player';

/**
 *
 */
export default class playerFactory
{
    constructor()
    {

    }

    async createByID(id)
    {
        let data = null;

        if(Number.isInteger(id))
            data = (await this._fetchUserByID(id)).data;

        let user = data.data.user;
        return new Player(id, user.name);
    }

    _fetchUserByID(id)
    {
        return axios({
            method: 'get',
            url: 'api/v1/user/'+id,
        });
    }
}
