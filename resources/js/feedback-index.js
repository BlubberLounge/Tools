/**
 * @author Maximilian Mewes
 *
 *
 */

document.addEventListener('DOMContentLoaded', function()
{
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let btnFeedbackRatingList = document.querySelectorAll('.btn-feedback-rating');
    let btnFeedbackHeaderList = document.querySelectorAll('button[data-bl-feedback-status="new"]');

    btnFeedbackRatingList.forEach(btn =>
    {
        btn.addEventListener('click', function(event)
        {
            let feedbackID = btn.closest('.accordion-item').dataset.blFeedbackId;

            fetch('/feedback/'+feedbackID, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status: btn.dataset.blFeedbackStatus,
                }),
            })
            .then(response => response.json())
            .then(data => {
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    btnFeedbackHeaderList.forEach(btn =>
    {
        let feedbackID = btn.closest('.accordion-item').dataset.blFeedbackId;

        const clickHandler = function(event)
        {
            fetch('/feedback/'+feedbackID, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status: 'seen',
                }),
            })
            .then(response => response.json())
            .then(data => {
                const icon = btn.closest('.feedback-seen-icon');
                if (icon) icon.style.display = 'block';
                btn.removeEventListener('click', clickHandler);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        };

        btn.addEventListener('click', clickHandler);
    });
});
