/**
 * @author Maximilian Mewes
 *
 *
 */

document.addEventListener('DOMContentLoaded', function()
{
    let formFeedbackCreate = document.getElementById('container-form-feedback-create');
    let inputFeedbackType = document.getElementById('input-feedback-type');
    let btnFeedbackTypeList = document.querySelectorAll('.btn-feedback');

    btnFeedbackTypeList.forEach(btn =>
    {
        btn.addEventListener('click', function(event)
        {
            document.getElementById('container-feedback').style.display = 'none';
            formFeedbackCreate.style.display = 'block';

            let feedbackType = btn.value;
            inputFeedbackType.value = feedbackType;
        });
    });
});
