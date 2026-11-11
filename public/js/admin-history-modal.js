(() => {
    const modal = document.getElementById('history-edit-modal');
    if (!modal) return;
    const form = document.getElementById('history-edit-form');
    const fields = document.getElementById('history-edit-fields');
    const error = document.getElementById('history-edit-error');
    const loading = document.getElementById('history-edit-loading');
    const save = document.getElementById('history-edit-save');
    let capture = null;
    let request = null;
    let saving = false;

    function showError(message) {
        error.textContent = message;
        error.hidden = false;
    }

    $(modal).on('show.bs.modal', async (event) => {
        const url = event.relatedTarget?.getAttribute('data-history-url');
        if (!url) return;
        request?.abort();
        request = new AbortController();
        const signal = request.signal;
        capture = null;
        fields.replaceChildren();
        error.hidden = true;
        loading.hidden = false;
        save.disabled = true;
        document.getElementById('history-edit-title').textContent = 'Edit history';
        document.getElementById('history-edit-date').textContent = '';
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}, signal});
            if (!response.ok) throw new Error('Unable to load saved results. Refresh the page and try again.');
            const data = await response.json();
            if (!data.draws || ![2, 3].includes(data.type)) throw new Error('Unable to load saved results. Please sign in again.');
            if (signal.aborted) return;
            capture = {type: data.type, date: data.date};
            document.getElementById('history-edit-title').textContent = `Edit MM ${data.type}D history`;
            document.getElementById('history-edit-date').textContent = `${data.date_label} · Myanmar time`;
            let editable = false;
            Object.entries(data.draws).forEach(([time, draw]) => {
                const label = document.createElement('label');
                label.className = 'form-field';
                const caption = document.createElement('span');
                caption.textContent = draw.label;
                const input = document.createElement('input');
                input.type = 'text';
                input.name = `numbers[${time}]`;
                input.inputMode = 'numeric';
                input.maxLength = data.type;
                input.pattern = `[0-9]{${data.type}}`;
                input.placeholder = '0'.repeat(data.type);
                input.value = draw.number ?? '';
                input.disabled = !draw.clock_id || !draw.due;
                editable ||= !input.disabled;
                label.append(caption, input);
                const hint = document.createElement('small');
                hint.className = 'muted';
                hint.textContent = !draw.clock_id ? 'Draw clock is not configured.' : !draw.due ? 'Available after this draw time.' : '';
                if (hint.textContent) label.append(hint);
                fields.append(label);
            });
            save.disabled = !editable;
            if ($(modal).hasClass('show')) fields.querySelector('input:not(:disabled)')?.focus();
        } catch (exception) {
            if (!signal.aborted) showError(exception instanceof SyntaxError ? 'Unable to load saved results. Refresh the page and sign in again.' : exception.message);
        } finally {
            if (!signal.aborted) loading.hidden = true;
        }
    });
    $(modal).on('shown.bs.modal', () => fields.querySelector('input:not(:disabled)')?.focus());
    $(modal).on('hide.bs.modal', (event) => {
        if (saving) event.preventDefault();
        else request?.abort();
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!capture || saving) return;
        const body = new FormData(form);
        body.set('type', capture.type);
        body.set('date', capture.date);
        saving = true;
        save.disabled = true;
        save.querySelector('span').textContent = 'Saving…';
        error.hidden = true;
        fields.querySelectorAll('[aria-invalid]').forEach(input => input.removeAttribute('aria-invalid'));
        fields.querySelectorAll('.field-error').forEach(node => node.remove());
        try {
            const response = await fetch(form.action, {method: 'POST', body, headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (!response.ok) {
                Object.entries(data.errors ?? {}).forEach(([key, messages]) => {
                    const input = Array.from(fields.querySelectorAll('input')).find(input => `numbers.${input.name.slice(8, -1)}` === key);
                    if (!input) return;
                    input.setAttribute('aria-invalid', 'true');
                    const message = document.createElement('p');
                    message.className = 'field-error';
                    message.textContent = messages.join(' ');
                    input.parentElement.append(message);
                });
                throw new Error(data.message || 'Unable to save history. Try again.');
            }
            // Refresh the same URL so calendar totals and all result displays agree.
            window.location.reload();
        } catch (exception) {
            showError(exception instanceof SyntaxError ? 'Unable to save history. Refresh the page and sign in again.' : exception.message);
            saving = false;
            save.disabled = false;
            save.querySelector('span').textContent = 'Save history';
            fields.querySelector('[aria-invalid="true"]')?.focus();
        }
    });
})();
