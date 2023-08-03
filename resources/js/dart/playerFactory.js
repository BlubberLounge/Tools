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
        let name = user.name;
        let fullName = user.firstname +' '+ user.lastname;

        return new Player(id, name, fullName);
    }

    _fetchUserByID(id)
    {
        return axios({
            method: 'get',
            url: '/api/v1/user/'+id,
        });
    }
}
