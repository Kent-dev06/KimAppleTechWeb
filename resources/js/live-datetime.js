const dateElements = document.querySelectorAll('[data-live-date]');
const timeElements = document.querySelectorAll('[data-live-time]');

function updateLiveDateTime() {
    const now = new Date();
    const date = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(now);
    const time = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    }).format(now);

    dateElements.forEach(element => {
        element.textContent = date;
    });
    timeElements.forEach(element => {
        element.textContent = time;
    });
}

if (dateElements.length || timeElements.length) {
    updateLiveDateTime();
    window.setInterval(updateLiveDateTime, 1000);
}
