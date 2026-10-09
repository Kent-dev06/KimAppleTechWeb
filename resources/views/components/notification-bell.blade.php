<div class="dropdown" data-notification-bell
    data-endpoint="{{ route('repair.notifications.index') }}"
    data-poll-url="{{ route('repair.notifications.poll') }}"
    data-read-url-prefix="{{ url('/repair/notifications') }}"
    data-mark-all-url="{{ route('repair.notifications.read-all') }}"
    data-user-channel="App.Models.User.{{ auth()->user()->getKey() }}"
    data-csrf="{{ csrf_token() }}">
    <button class="btn btn-outline-light btn-sm position-relative" type="button" data-notification-toggle aria-expanded="false" aria-label="Notifications">
        <svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" data-notification-count hidden>0</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0 shadow" data-notification-menu style="width:min(23rem, calc(100vw - 2rem)); max-height:27rem; overflow-y:auto">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong>Notifications</strong>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button" data-test-notification-sound>Test sound</button>
                <button class="btn btn-link btn-sm p-0 text-decoration-none" type="button" data-mark-all-read>Mark all as read</button>
            </div>
        </div>
        <ul class="list-group list-group-flush" data-notification-list>
            <li class="list-group-item small text-secondary text-center py-3" data-notification-empty>Loading notifications…</li>
        </ul>
    </div>
    <div class="toast-container position-fixed top-0 end-0 p-3" data-notification-toasts aria-live="polite" aria-atomic="true" style="z-index:1090"></div>
</div>
