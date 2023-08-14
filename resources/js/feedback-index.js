/**
 * @author Maximilian Mewes
 *
 *
 */

$(function()
{
    let btnFeedbackRatingList = $('.btn-feedback-rating');
    let btnFeedbackHeaderList = $('button[data-bl-feedback-status="new"]');

    btnFeedbackRatingList.each((k, e) =>
    {
        $(e).click(event =>
        {
            let feedbackID = $(e).closest('.accordion-item').data('bl-feedback-id');

            $.ajax({
                url: '/feedback/'+feedbackID,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    status: $(e).data('bl-feedback-status'),
                },
                beforeSend: function() {
                },
                success: function(response) {
                    // console.log(response);
                    // most cheap
                    window.location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // handle the error case
                    // console.log(errorThrown);
                    // TODO
                }
            });
        });
    });

    btnFeedbackHeaderList.each((k, e) =>
    {
        let feedbackID = $(e).closest('.accordion-item').data('bl-feedback-id');

        $(e).click(event =>
        {
            $.ajax({
                url: '/feedback/'+feedbackID,
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    status: 'seen',
                },
                beforeSend: function() {
                },
                success: function(response) {
                    // console.log(response);
                    $(e).closest('.feedback-seen-icon').show();
                    $(e).off('click'); // remove event listener
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // handle the error case
                    // console.log(errorThrown);
                    // TODO
                }
            });
        });
    });
});
