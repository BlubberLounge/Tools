/**
 * @author Maximilian Mewes
 *
 *
 */
$(function() {

});

const TimetableStaus = {
    AVAILABLE: 'available',
    MAYBE: 'maybe',
    NOTIME: 'noTime',
};


/**
 *
 *
 */
class TimetableElementFactory
{
    TimetableCellStatus = null;

    constructor()
    {
        this.baseContainer = $('<div></div>');
        this.baseInput = $('<input type="radio" class="btn-check" autocomplete="off">');
        this.baseLabel = $('<label class="btn btn-timetable"></label>');
    }

    isChecked(s)
    {
        return this.TimetableCellStatus === s;
    }

    getButtonClass(s)
    {
        if(TimetableStaus.AVAILABLE == s) {
            return 'btn-bl-success';
        }else if(TimetableStaus.MAYBE == s) {
            return 'btn-bl-warning';
        }else if(TimetableStaus.NOTIME == s) {
            return 'btn-bl-danger';
        } else {
            return 'btn-dark';
        }
    }

    getIcon(s)
    {
        if(TimetableStaus.AVAILABLE == s) {
            return '<i class="fa-solid fa-check"></i>';
        }else if(TimetableStaus.MAYBE == s) {
            return '<i class="fa-solid fa-question"></i>';
        }else if(TimetableStaus.NOTIME == s) {
            return '<i class="fa-solid fa-xmark"></i>';
        } else {
            return '<i class="fa-solid fa-bug"></i>';
        }
    }

    createInput(s)
    {
        let newInput = this.baseInput.clone();
        newInput.attr('name', 'btn-timetable-status');
        newInput.attr('id', 'btn-'+s);
        newInput.attr('checked', this.isChecked(s));

        return newInput;
    }

    createLabel(s)
    {
        let newLabel = this.baseLabel.clone();
        newLabel.addClass(this.getButtonClass(s));
        newLabel.attr('for', 'btn-'+s);
        newLabel.html(this.getIcon(s));

        return newLabel;
    }
}


/**
 *
 *
 */
class Timetable
{
	constructor(tableID = undefined)
    {
        this.apiBaseURL = '/api/v1';
    	this.tableID = tableID;
        this.factory = new TimetableElementFactory;
        this.init();
    }

    init()
    {
        this.addStickyDetector();
    }

    addStickyDetector()
    {
        const el = document.querySelector(".detectSticky")
        const observer = new IntersectionObserver(
          ([e]) => {
            let el = $(".timeTableUser");
            el.toggleClass("isStuck", e.intersectionRatio < 1);
        },
          { threshold: [1] }
        );

        observer.observe(el);
    }

    getActions(clickedElement)
    {
        // convert to JQuery
        let element = $(clickedElement);
        let date = moment(element.attr('data-bl-timetable-date'));
        let status = TimetableStaus[element.attr('data-bl-timetable-status').toUpperCase()];
        // TODO clenup
        if(status === undefined)
            console.log('wrong status: ' + element.attr('data-bl-timetable-status').toUpperCase());

        this.factory.TimetableCellStatus = status;

        let container = $('<div id="timetable-container" class="d-flex justify-center"></div>');
        let form = $('<form id="timetable-form" class="justify-center" style="display: flex;"></form>');

        // create elements
        let inputAvailable = this.factory.createInput('available');
        let labelAvailable = this.factory.createLabel('available');
        form.append(inputAvailable);
        form.append(labelAvailable);

        let inputMaybe = this.factory.createInput('maybe');
        let labelMaybe = this.factory.createLabel('maybe');
        form.append(inputMaybe);
        form.append(labelMaybe);

        let labelNoTime = this.factory.createLabel('noTime');
        let inputNoTime = this.factory.createInput('noTime');
        form.append(inputNoTime);
        form.append(labelNoTime);

        // Add onClick event listener
        $(form).children('label').each((k, e) =>
        {
            $(e).on('click', el =>
            {
                $.ajax({
                    url: this.apiBaseURL+'/timetable/'+date.format('YYYY-MM-DD'),
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                    data: {
                        status: TimetableStaus[$(e).attr('for').replace('btn-', '').toUpperCase()]
                    },
                    beforeSend: function() {
                        $('#timetable-form').hide();
                        $('#loadingSpinnerContainer').show();
                    },
                    success: function(response) {
                        // looks kinda weird when this switch happens and the popover gets closed right after that
                        // $('#timetable-form').show();
                        // $('#loadingSpinnerContainer').hide();
                        element.attr('data-bl-timetable-status', response.data.status);
                        element.popover('hide');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // console.log(errorThrown);
                        // handle the error case
                    }
                });
            });
        });

        let loadingSpinnerContainer = document.createElement("div");
        loadingSpinnerContainer.setAttribute('style', 'display: none;font-size:2rem;');
        loadingSpinnerContainer.id = 'loadingSpinnerContainer';

        let loadingSpinner = document.createElement("i");
        loadingSpinner.classList.add('fa-solid', 'fa-spinner', 'fa-spin-pulse');

        loadingSpinnerContainer.append(loadingSpinner);
        container.append(form);
        container.append(loadingSpinnerContainer);

        return container;
    }

    updateAcquaintance(userId, showOnHomeView)
    {
        $.ajax({
            url: this.apiBaseURL+'/acquaintance/byReceiverOrTransmitter/'+userId,
            method: 'PUT',
            data: {
                showOnHomeView: parseInt(showOnHomeView)
            },
            beforeSend: function() {
            },
            success: function(response) {
                // console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // handle the error case
                // console.log(errorThrown);
            }
        });
    }

    addRow(userId, rowIndex)
    {
        $.ajax({
            // may use `/user/{user}/timetable/between/{dateFrom}/{dateTo}` later
            url: this.apiBaseURL+'/user/'+userId+'/timetable',
            method: 'GET',
            data: {
            },
            beforeSend: function() {
            },
            success: function(response) {
                let data = response.data.timetable;
                let user = response.data.user;

                if(data === undefined || data.length <= 0)
                    data = [];
                let t = new Timetable;
                t.updateAcquaintance(userId, 1);

                let newRow = $('<tr></tr>')
                    .append($('<td class="detectSticky"></td>'))
                    .append($('<td class="timeTableUser">'+user.name+'</td>'));
                let newCell = undefined;

                newRow.attr('data-bl-timetable-user-id', userId);

                for(let i=0; i <= 30; i++)
                {
                    let d = data[i];
                    let calculatedDate = moment().add(i, 'days');
                    var foundEntry = undefined;
                    // TODO will most likely cause problems with more data
                    data.forEach((item, index) => {
                        // console.table([item.date, date]);
                        let date = calculatedDate.format('YYYY-MM-DD');
                        if(item.date == date)
                            foundEntry = item;
                    });

                    if(foundEntry !== undefined) {
                        newCell = $('<td data-bl-timetable-status="'+TimetableStaus[foundEntry.status.toUpperCase()]+'"></td>');
                    } else {
                        newCell = $('<td data-bl-timetable-status="'+TimetableStaus['noTime'.toUpperCase()]+'"></td>');
                    }

                    newRow.append(newCell);
                }

                // TODO please fix this .. omg
                // let activeRows = $('#timetableTable tbody').children().length-3;
                // let rIndex = parseInt(rowIndex)-1;
                // let insertAt = activeRows <= 0 || rIndex <= 1
                //     ? 0
                //     : rIndex >= activeRows
                //         ? activeRows
                //         : rIndex;

                // if(insertAt <= 0) {
                //     $('#timetableTable tbody > tr:nth-child(1)').before(newRow);
                // } else {
                //     $('#timetableTable tbody > tr:nth-child('+insertAt+')').after(newRow);
                // }

                $('#timetableTable tbody > tr:nth-child(1)').before(newRow);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // handle the error case
                // console.log(errorThrown);
            }
        });
    }

    removeRow(userId, row)
    {
        let r = $(row);
        if(r === undefined)
            return;

        r.remove();

        this.updateAcquaintance(userId, 0);
    }
}

// EXPORT
module.exports = Timetable;
