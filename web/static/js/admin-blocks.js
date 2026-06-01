// Block editor: a small state-driven editor for a page's content blocks.
// State is an array of { id, block_id, type, data }. Structural changes
// (add/remove/move/list-rows) trigger a re-render; text edits mutate state in
// place. On submit the whole array is serialised into a hidden input.
(function () {
    'use strict';

    const editorEl = document.getElementById('blockEditor');
    if (!editorEl) return;

    const parse = (id, fallback) => {
        try { return JSON.parse(document.getElementById(id).textContent) || fallback; }
        catch (_) { return fallback; }
    };

    const schema = parse('blocksSchema', []);
    let blocks = parse('blocksData', []);
    let media = parse('mediaData', []);

    const schemaFor = (type) => schema.find((d) => d.type === type);
    const labelFor = (type) => (schemaFor(type) || { label: type }).label;
    const uid = () => (crypto.randomUUID ? crypto.randomUUID() : 'b-' + Math.random().toString(36).slice(2));

    // --- Block type selector + add button ---
    const typeSelect = document.getElementById('blockTypeSelect');
    schema.forEach((def) => {
        const opt = document.createElement('option');
        opt.value = def.type;
        opt.textContent = def.label;
        typeSelect.appendChild(opt);
    });
    document.getElementById('addBlockBtn').addEventListener('click', () => {
        blocks.push({ id: 0, block_id: uid(), type: typeSelect.value, data: {} });
        render();
    });

    // --- Rendering ---
    function render() {
        editorEl.innerHTML = '';
        if (!blocks.length) {
            editorEl.innerHTML = '<p class="muted">Noch keine Blöcke. Füge oben einen hinzu.</p>';
            return;
        }
        blocks.forEach((block, index) => editorEl.appendChild(renderBlock(block, index)));
    }

    function renderBlock(block, index) {
        const def = schemaFor(block.type);
        const card = el('div', 'block-card');

        const head = el('div', 'block-card__head');
        head.appendChild(el('span', 'block-card__type', labelFor(block.type)));
        const actions = el('div', 'block-card__actions');
        actions.appendChild(iconBtn('↑', 'Nach oben', () => move(index, -1)));
        actions.appendChild(iconBtn('↓', 'Nach unten', () => move(index, 1)));
        actions.appendChild(iconBtn('✕', 'Entfernen', () => removeBlock(index), 'is-danger'));
        head.appendChild(actions);
        card.appendChild(head);

        const body = el('div', 'block-card__body');
        if (def) {
            def.fields.forEach((field) => body.appendChild(renderField(block, field)));
        } else {
            body.appendChild(el('p', 'muted', 'Unbekannter Blocktyp: ' + block.type));
        }
        card.appendChild(body);
        return card;
    }

    function renderField(block, field) {
        const wrap = el('label', 'block-field');
        wrap.appendChild(el('span', 'block-field__label', field.label));

        if (field.kind === 'list') {
            wrap.classList.add('block-field--list');
            wrap.appendChild(renderList(block, field));
            return wrap;
        }
        if (field.kind === 'image') {
            wrap.appendChild(renderImage(block, field));
            return wrap;
        }

        let input;
        if (field.kind === 'textarea' || field.kind === 'html') {
            input = document.createElement('textarea');
            input.rows = field.kind === 'html' ? 4 : 3;
            if (field.kind === 'html') wrap.appendChild(el('span', 'hint', 'HTML erlaubt'));
        } else {
            input = document.createElement('input');
            input.type = 'text';
        }
        input.value = block.data[field.key] != null ? block.data[field.key] : '';
        input.addEventListener('input', () => { block.data[field.key] = input.value; });
        wrap.appendChild(input);
        return wrap;
    }

    function renderImage(block, field) {
        const box = el('div', 'image-field');
        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = '/media/…';
        input.value = block.data[field.key] != null ? block.data[field.key] : '';
        const preview = el('div', 'image-field__preview');
        const updatePreview = () => {
            preview.innerHTML = input.value ? '' : '';
            if (input.value) {
                const img = document.createElement('img');
                img.src = input.value;
                preview.appendChild(img);
            }
        };
        input.addEventListener('input', () => { block.data[field.key] = input.value; updatePreview(); });

        const pick = el('button', 'btn btn--small', 'Medien wählen');
        pick.type = 'button';
        pick.addEventListener('click', () => openMediaPicker((url) => {
            input.value = url; block.data[field.key] = url; updatePreview();
        }));

        const row = el('div', 'image-field__row');
        row.appendChild(input);
        row.appendChild(pick);
        box.appendChild(row);
        box.appendChild(preview);
        updatePreview();
        return box;
    }

    function renderList(block, field) {
        const container = el('div', 'list-field');
        if (!Array.isArray(block.data[field.key])) block.data[field.key] = [];
        const items = block.data[field.key];

        items.forEach((item, i) => {
            const row = el('div', 'list-row');
            field.fields.forEach((sub) => {
                const cell = el('label', 'block-field');
                cell.appendChild(el('span', 'block-field__label', sub.label));
                let input;
                if (sub.kind === 'textarea' || sub.kind === 'html') {
                    input = document.createElement('textarea');
                    input.rows = 2;
                } else {
                    input = document.createElement('input');
                    input.type = 'text';
                }
                input.value = item[sub.key] != null ? item[sub.key] : '';
                input.addEventListener('input', () => { item[sub.key] = input.value; });
                cell.appendChild(input);
                row.appendChild(cell);
            });
            row.appendChild(iconBtn('✕', 'Zeile entfernen', () => { items.splice(i, 1); render(); }, 'is-danger'));
            container.appendChild(row);
        });

        const add = el('button', 'btn btn--small', '+ Eintrag');
        add.type = 'button';
        add.addEventListener('click', () => { items.push({}); render(); });
        container.appendChild(add);
        return container;
    }

    // --- Structural operations ---
    function move(index, delta) {
        const target = index + delta;
        if (target < 0 || target >= blocks.length) return;
        const [b] = blocks.splice(index, 1);
        blocks.splice(target, 0, b);
        render();
    }
    function removeBlock(index) {
        if (!confirm('Diesen Block wirklich entfernen?')) return;
        blocks.splice(index, 1);
        render();
    }

    // --- Media picker overlay ---
    function openMediaPicker(onPick) {
        const overlay = el('div', 'media-picker');
        const panel = el('div', 'media-picker__panel');
        const head = el('div', 'media-picker__head');
        head.appendChild(el('h3', '', 'Medien'));
        const close = el('button', 'btn btn--small', 'Schließen');
        close.type = 'button';
        close.addEventListener('click', () => overlay.remove());
        head.appendChild(close);
        panel.appendChild(head);

        const upload = el('div', 'media-picker__upload');
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        const uploadBtn = el('button', 'btn btn--small btn--primary', 'Hochladen');
        uploadBtn.type = 'button';
        const status = el('span', 'hint', '');
        uploadBtn.addEventListener('click', async () => {
            if (!fileInput.files.length) { status.textContent = 'Bitte Datei wählen.'; return; }
            status.textContent = 'Lädt…';
            const fd = new FormData();
            fd.append('file', fileInput.files[0]);
            try {
                const res = await fetch('/admin/media', { method: 'POST', headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' }, body: fd });
                const data = await res.json();
                if (data.success) {
                    media.unshift({ url: data.url, name: data.name });
                    status.textContent = '';
                    onPick(data.url);
                    overlay.remove();
                } else {
                    status.textContent = data.message || 'Fehler.';
                }
            } catch (_) { status.textContent = 'Upload fehlgeschlagen.'; }
        });
        upload.appendChild(fileInput);
        upload.appendChild(uploadBtn);
        upload.appendChild(status);
        panel.appendChild(upload);

        const grid = el('div', 'media-picker__grid');
        if (!media.length) {
            grid.appendChild(el('p', 'muted', 'Noch keine Medien. Lade oben ein Bild hoch.'));
        }
        media.forEach((m) => {
            const tile = el('button', 'media-picker__tile');
            tile.type = 'button';
            const img = document.createElement('img');
            img.src = m.url;
            img.alt = m.name;
            img.loading = 'lazy';
            tile.appendChild(img);
            tile.addEventListener('click', () => { onPick(m.url); overlay.remove(); });
            grid.appendChild(tile);
        });
        panel.appendChild(grid);

        overlay.appendChild(panel);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
        document.body.appendChild(overlay);
    }

    // --- Serialise on submit ---
    document.getElementById('blockEditorForm').addEventListener('submit', () => {
        document.getElementById('blocksInput').value = JSON.stringify(blocks);
    });

    // --- DOM helpers ---
    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text != null) node.textContent = text;
        return node;
    }
    function iconBtn(symbol, title, onClick, extra) {
        const b = el('button', 'icon-btn' + (extra ? ' ' + extra : ''), symbol);
        b.type = 'button';
        b.title = title;
        b.addEventListener('click', onClick);
        return b;
    }

    render();
})();
