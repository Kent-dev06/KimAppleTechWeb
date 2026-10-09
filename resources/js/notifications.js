(() => {
    const roots = document.querySelectorAll('[data-notification-bell]');

    if (!roots.length) return;

    roots.forEach(root => {
        const countBadge = root.querySelector('[data-notification-count]');
        const list = root.querySelector('[data-notification-list]');
        const menu = root.querySelector('[data-notification-menu]');
        const toggle = root.querySelector('[data-notification-toggle]');
        const markAll = root.querySelector('[data-mark-all-read]');
        const toasts = root.querySelector('[data-notification-toasts]');
        const knownIds = new Set();
        let unreadCount = 0;
        let pollTimer = null;
        let subscribed = false;

        const setCount = count => {
            unreadCount = Math.max(0, Number(count) || 0);
            countBadge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
            countBadge.hidden = unreadCount === 0;
            markAll.disabled = unreadCount === 0;
        };

        const showToast = message => {
            const toast = document.createElement('div');
            toast.className = 'toast show align-items-center text-bg-dark border-0 mb-2';
            toast.setAttribute('role', 'status');
            toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button></div>';
            toast.querySelector('.toast-body').textContent = message;
            toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
            toasts.append(toast);
            window.setTimeout(() => toast.remove(), 5000);
        };

        const renderItems = items => {
            list.replaceChildren();

            if (!items.length) {
                const empty = document.createElement('li');
                empty.className = 'list-group-item small text-secondary text-center py-3';
                empty.textContent = 'You’re all caught up.';
                list.append(empty);
                return;
            }

            items.slice(0, 10).forEach(item => {
                if (!item.notification_id) return;
                knownIds.add(item.notification_id);

                const row = document.createElement('li');
                row.className = `list-group-item px-3 py-2${item.read_at ? '' : ' bg-light'}`;
                row.dataset.notificationId = item.notification_id;

                const content = document.createElement('div');
                content.className = 'd-flex align-items-start justify-content-between gap-2';

                const link = document.createElement('a');
                link.className = 'small text-decoration-none text-body flex-grow-1';
                link.href = item.link || '/repair';

                const message = document.createElement('span');
                message.className = 'd-block';
                message.textContent = item.message || 'New repair update.';
                link.append(message);

                if (item.created_at) {
                    const time = document.createElement('small');
                    time.className = 'text-secondary';
                    time.textContent = new Date(item.created_at).toLocaleString();
                    link.append(time);
                }

                content.append(link);

                if (!item.read_at) {
                    const read = document.createElement('button');
                    read.type = 'button';
                    read.className = 'btn btn-link btn-sm p-0 text-nowrap';
                    read.textContent = 'Mark as read';
                    read.dataset.markRead = item.notification_id;
                    content.append(read);
                }

                row.append(content);
                list.append(row);
            });

            if (!list.children.length) {
                const empty = document.createElement('li');
                empty.className = 'list-group-item small text-secondary text-center py-3';
                empty.textContent = 'You’re all caught up.';
                list.append(empty);
            }
        };

        const refresh = async (notifyNew = false) => {
            const response = await fetch(notifyNew ? root.dataset.pollUrl : root.dataset.endpoint, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) throw new Error('Notification refresh failed.');

            const data = await response.json();
            if (notifyNew) {
                data.items.forEach(item => {
                    if (!knownIds.has(item.notification_id)) showToast(item.message);
                });
            }
            setCount(data.unread_count);
            renderItems(data.items);
        };

        const startPolling = () => {
            if (!pollTimer) pollTimer = window.setInterval(() => refresh(true).catch(() => {}), 20000);
        };

        const stopPolling = () => {
            if (pollTimer) window.clearInterval(pollTimer);
            pollTimer = null;
        };

        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('show');
            toggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', event => {
            if (!root.contains(event.target)) {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        root.addEventListener('click', async event => {
            const readButton = event.target.closest('[data-mark-read]');

            try {
                if (readButton) {
                    const id = readButton.dataset.markRead;
                    const response = await fetch(`${root.dataset.readUrlPrefix}/${encodeURIComponent(id)}/read`, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf },
                        credentials: 'same-origin',
                    });
                    if (response.ok) await refresh();
                } else if (event.target.closest('[data-mark-all-read]')) {
                    const response = await fetch(root.dataset.markAllUrl, {
                        method: 'POST',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf },
                        credentials: 'same-origin',
                    });
                    if (response.ok) await refresh();
                }
            } catch (_) {
                // The next poll will reconcile the badge and list.
            }
        });

        startPolling();
        refresh().catch(() => renderItems([]));

        try {
            if (window.Echo && root.dataset.userChannel) {
                const channel = window.Echo.private(root.dataset.userChannel);

                channel.listen('.repair.notification.created', item => {
                    if (!item.notification_id || knownIds.has(item.notification_id)) return;
                    knownIds.add(item.notification_id);
                    item.read_at = null;
                    renderItems([item, ...Array.from(list.querySelectorAll('[data-notification-id]')).map(row => ({
                        notification_id: row.dataset.notificationId,
                        message: row.querySelector('a span')?.textContent || '',
                        link: row.querySelector('a')?.href || '/repair',
                        read_at: row.classList.contains('bg-light') ? null : new Date().toISOString(),
                    }))]);
                    setCount(unreadCount + 1);
                    showToast(item.message || 'New repair update.');
                });

                channel.subscribed(() => {
                    subscribed = true;
                    stopPolling();
                });
                channel.error(() => {
                    subscribed = false;
                    startPolling();
                });

                const connection = window.Echo.connector?.pusher?.connection;
                connection?.bind('disconnected', startPolling);
                connection?.bind('unavailable', startPolling);
                connection?.bind('failed', startPolling);
                connection?.bind('connected', () => {
                    if (subscribed) stopPolling();
                });
            }
        } catch (_) {
            startPolling();
        }
    });
})();
