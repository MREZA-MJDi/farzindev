const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
}[character]));

function setupEditor(root) {
    const surface = root.querySelector('[data-editor-surface]');
    const source = root.querySelector('[data-editor-source]');
    const form = root.closest('form');

    if (!surface || !source || !form) return;

    surface.hidden = false;
    source.hidden = true;
    surface.setAttribute('role', 'textbox');
    surface.setAttribute('aria-multiline', 'true');
    surface.setAttribute('aria-label', 'متن مقاله');
    surface.setAttribute('contenteditable', 'true');
    surface.setAttribute('spellcheck', 'true');

    const sync = () => {
        source.value = surface.innerHTML.trim();
        root.querySelector('[data-editor-word-count]')?.replaceChildren(
            document.createTextNode(new Intl.NumberFormat('fa-IR').format(
                (surface.innerText.trim().match(/\S+/gu) || []).length
            )),
        );
    };

    root.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('click', () => {
            const command = button.dataset.editorCommand;
            surface.focus();

            if (command === 'createLink') {
                const rawUrl = window.prompt('آدرس لینک را وارد کنید (HTTPS یا مسیر داخلی):');
                if (!rawUrl) return;
                const url = rawUrl.trim();

                if (!(/^(https:\/\/|\/(?!\/)|mailto:|tel:)/i.test(url))) {
                    window.alert('فقط لینک HTTPS یا مسیر داخلی مجاز است.');
                    return;
                }

                document.execCommand('createLink', false, url);
                surface.querySelectorAll('a[target="_blank"]').forEach((link) => {
                    link.setAttribute('rel', 'noopener noreferrer');
                });
            } else {
                document.execCommand(command, false, null);
            }

            sync();
        });
    });

    root.querySelector('[data-editor-block]')?.addEventListener('change', (event) => {
        const block = event.currentTarget.value;
        if (!block) return;

        surface.focus();
        document.execCommand('formatBlock', false, block);
        event.currentTarget.value = '';
        sync();
    });

    root.querySelector('[data-editor-insert-image]')?.addEventListener('click', () => {
        const rawUrl = window.prompt('آدرس HTTPS یا مسیر تصویر در /storage/ یا /images/ را وارد کنید:');
        if (!rawUrl) return;

        const url = rawUrl.trim();
        if (!(/^https:\/\//i.test(url) || /^\/(storage|images)\//i.test(url))) {
            window.alert('آدرس تصویر باید HTTPS یا مسیر داخلی مجاز باشد.');
            return;
        }

        const alt = window.prompt('متن جایگزین تصویر (Alt):') || '';
        surface.focus();
        document.execCommand('insertHTML', false,
            '<img src="' + escapeHtml(url) + '" alt="' + escapeHtml(alt) + '" loading="lazy">');
        sync();
    });

    surface.addEventListener('input', sync);
    surface.addEventListener('paste', (event) => {
        event.preventDefault();
        const text = event.clipboardData?.getData('text/plain') || '';
        document.execCommand('insertText', false, text);
        sync();
    });
    surface.addEventListener('blur', sync);
    form.addEventListener('submit', sync);

    sync();
}

document.querySelectorAll('[data-blog-rich-editor]').forEach(setupEditor);
