import GameType from "./enums/gameType";
import GameSettings from "./gameSettings";

/**
 *
 *
 *
 */
export default class Game
{
    constructor()
    {
        this.id = null;
        this.createdBy = null;
        this.type = null;
        this.status = null;
        this.private = false;

        this.title = null;
        this.comment = null;

        this.points = undefined;
        this.start = undefined;
        this.end = undefined;
        this.fields = undefined;

        this.singleOut = true;
        this.doubleOut = true;
        this.trippleOut = true;
        this.singleIn = true;
        this.doubleIn = true;
        this.trippleIn = true;

        this.settings = new GameSettings();

        this._init();
    }

    _init()
    {
        console.info('<========== Initalising DartGame ==========>');
        this.id = document.getElementById('gameId').getAttribute('value');


        let details = this._fetchDetails().game;

        this.createdBy = details.created_by.firstname +' '+ details.created_by.lastname;
        this.type = GameType.fromString(details.type);
        this.status = details.status;
        this.private = details.private;

        this.title = details.title;
        this.comment = details.comment;

        if(details.points)
            this.points = details.points;

        if(details.start)
            this.start = details.start;

        if(details.end)
            this.end = details.end;

        if(details.fields)
            this.fields = details.fields;

        this.singleOut = details.singleOut;
        this.doubleOut = details.doubleOut;
        this.trippleOut = details.trippleOut;
        this.singleIn = details.singleIn;
        this.doubleIn = details.doubleIn;
        this.trippleIn = details.trippleIn;
    }

    _fetchDetails()
    {
        // let result = await axios({
        //     method: 'get',
        //     url: '/api/v1/dart/'+this.id,
        // }).data;

         let result = $.ajax({
            url: '/api/v1/dart/'+this.id,
            type: "GET",
            async: false,
            success: function (data)
            {
                return data.data;
            },
            error: function (xhr, exception) {
                var msg = "";
                if (xhr.status === 0) {
                    msg = "Not connect.\n Verify Network." + xhr.responseText;
                } else if (xhr.status == 404) {
                    msg = "Requested page not found. [404]" + xhr.responseText;
                } else if (xhr.status == 500) {
                    msg = "Internal Server Error [500]." +  xhr.responseText;
                } else if (exception === "parsererror") {
                    msg = "Requested JSON parse failed.";
                } else if (exception === "timeout") {
                    msg = "Time out error." + xhr.responseText;
                } else if (exception === "abort") {
                    msg = "Ajax request aborted.";
                } else {
                    msg = "Error:" + xhr.status + " " + xhr.responseText;
                }

                console.error(msg);
                return null;
            }
        });

        return result.responseJSON.data;
    }

    run()
    {

    }
}
