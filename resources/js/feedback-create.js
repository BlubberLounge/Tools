/**
 * @author Maximilian Mewes
 *
 *
 */

$(function()
{
    let formFeedbackCreate = $('#container-form-feedback-create');
    let formFeedback = $('#form-feedback');
    let inputFeedbackType = $('#input-feedback-type');
    let btnFeedbackTypeList = $('.btn-feedback');

    btnFeedbackTypeList.each((k, e) =>
    {
        $(e).click(event =>
        {
            $('#container-feedback').hide();
            formFeedbackCreate.show();

            let feedbackType = $(e).val();
            inputFeedbackType.val(feedbackType);
        });
    });
});
