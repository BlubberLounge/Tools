
const btnQueueAdd = document.getElementById('btnQueueAdd');
const btnQueueRemove = document.getElementById('btnQueueRemove');

if (btnQueueAdd) {
    btnQueueAdd.addEventListener('click', e => {
        axios.post(`/api/v1/dart/queue/add`).then(response => {
            // Create new queue item with new design
            const queueList = document.getElementById('dartQueueList');
            if (!queueList) return;

            // Create queue item
            const newItem = document.createElement('div');
            newItem.className = 'queue-item';
            newItem.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-500 font-semibold text-sm">
                    ${queueList.children.length + 1}
                </div>
                <div class="flex-1">
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ __('me') }}</span>
                    <span class="text-slate-400 dark:text-slate-500 text-sm ml-2">gerade eben</span>
                </div>
                <button id="btnQueueRemove" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            // Remove empty state if exists
            const emptyState = queueList.querySelector('.text-center.py-8');
            if (emptyState) {
                emptyState.remove();
            }

            queueList.appendChild(newItem);

            // Update status badge
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                if (badge.classList.contains('danger')) {
                    badge.classList.remove('danger', 'bg-red-500/20', 'text-red-600', 'dark:text-red-400');
                    badge.classList.add('success', 'bg-green-500/20', 'text-green-600', 'dark:text-green-400');
                    badge.innerHTML = '<i class="fa-solid fa-check text-xs"></i> in queue';
                }
            });

            // Disable add button
            btnQueueAdd.disabled = true;
            btnQueueAdd.classList.add('opacity-50', 'cursor-not-allowed');

            // Add event listener to new remove button
            const newRemoveBtn = newItem.querySelector('#btnQueueRemove');
            if (newRemoveBtn) {
                addRemoveEventListener(newRemoveBtn);
            }
        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });
}

if (btnQueueRemove) {
    addRemoveEventListener(btnQueueRemove);
}

function addRemoveEventListener(element) {
    if (!element) return;

    element.addEventListener('click', e => {
        axios.post(`/api/v1/dart/queue/remove`).then(response => {
            // Remove the queue item
            const queueItem = e.target.closest('.queue-item') || e.target.closest('li');
            if (queueItem) {
                queueItem.remove();
            }

            // Update status badge
            const statusBadges = document.querySelectorAll('.status-badge');
            statusBadges.forEach(badge => {
                if (badge.classList.contains('success')) {
                    badge.classList.remove('success', 'bg-green-500/20', 'text-green-600', 'dark:text-green-400');
                    badge.classList.add('danger', 'bg-red-500/20', 'text-red-600', 'dark:text-red-400');
                    badge.innerHTML = '<i class="fa-solid fa-xmark text-xs"></i> not in queue';
                }
            });

            // Enable add button
            if (btnQueueAdd) {
                btnQueueAdd.disabled = false;
                btnQueueAdd.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            // Show empty state if queue is empty
            const queueList = document.getElementById('dartQueueList');
            if (queueList && queueList.querySelectorAll('.queue-item').length === 0) {
                queueList.innerHTML = `
                    <div class="text-center py-8">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                            <i class="fa-solid fa-inbox text-xl text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">queue is empty</p>
                    </div>
                `;
            }
        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    });
}
