(() => {
    document.querySelectorAll('[data-appointment-time-form]').forEach(form => {
        const start = form.elements.namedItem(form.dataset.startField);
        const end = form.elements.namedItem(form.dataset.endField);
        const date = form.dataset.dateField ? form.elements.namedItem(form.dataset.dateField) : null;
        const status = form.dataset.statusField ? form.elements.namedItem(form.dataset.statusField) : null;
        const minDuration = Number(form.dataset.minDurationMinutes || 30);
        const maxDuration = Number(form.dataset.maxDurationMinutes || 120);
        const openTime = form.dataset.openTime || '08:00';
        const closeTime = form.dataset.closeTime || '17:00';

        if (!start || !end) return;

        const minutes = value => {
            const [hours, mins] = value.split(':').map(Number);
            return (hours * 60) + mins;
        };

        const setError = (input, message) => {
            input.setCustomValidity(message);
            input.classList.toggle('is-invalid', Boolean(message));
            const feedback = form.querySelector(`[data-time-error-for="${input.name}"]`);
            if (feedback) {
                feedback.textContent = message;
                feedback.classList.toggle('d-block', Boolean(message));
            }
        };

        const validate = () => {
            const scheduleRequired = !status || ['Confirmed', 'Rescheduled'].includes(status.value);
            setError(start, '');
            setError(end, '');
            if (date) setError(date, '');

            if (!scheduleRequired) return true;

            let valid = true;
            const startValue = start.value;
            const endValue = end.value;

            if (date && !date.value) {
                setError(date, 'Choose a confirmed date.');
                valid = false;
            }

            if (!startValue) {
                setError(start, 'Choose a start time.');
                valid = false;
            } else if (minutes(startValue) < minutes(openTime) || minutes(startValue) > minutes(closeTime) - minDuration) {
                setError(start, `Choose a start time between ${openTime} and ${closeTime}.`);
                valid = false;
            } else if (minutes(startValue) % 30 !== 0) {
                setError(start, 'Choose a 30-minute time slot.');
                valid = false;
            }

            if (!endValue) {
                setError(end, 'Choose an end time.');
                valid = false;
            } else if (minutes(endValue) <= minutes(openTime) || minutes(endValue) > minutes(closeTime)) {
                setError(end, `Choose an end time between ${openTime} and ${closeTime}.`);
                valid = false;
            } else if (minutes(endValue) % 30 !== 0) {
                setError(end, 'Choose a 30-minute time slot.');
                valid = false;
            }

            if (startValue && endValue) {
                const duration = minutes(endValue) - minutes(startValue);
                if (duration <= 0) {
                    setError(end, 'The end time must be after the start time.');
                    valid = false;
                } else if (duration < minDuration || duration > maxDuration) {
                    setError(end, `Appointment length must be ${minDuration} to ${maxDuration} minutes.`);
                    valid = false;
                }
            }

            if (date?.value && date.min && date.value < date.min) {
                setError(date, 'Choose today or a future date.');
                valid = false;
            }

            return valid;
        };

        form.addEventListener('input', validate);
        form.addEventListener('change', validate);
        form.addEventListener('invalid', event => {
            if (event.target === start || event.target === end || event.target === date) validate();
        }, true);
        form.addEventListener('submit', event => {
            if (!validate()) {
                event.preventDefault();
                form.querySelector('.is-invalid')?.focus();
            }
        });
    });
})();
