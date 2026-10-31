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

    let savedRange = null;

    const captureSelection = () => {
        const selection = window.getSelection();
        if (
            selection?.rangeCount
            && surface.contains(selection.anchorNode)
            && surface.contains(selection.focusNode)
        ) {
            savedRange = selection.getRangeAt(0).cloneRange();
        }
    };

    const restoreSelection = () => {
        const selection = window.getSelection();
        if (!savedRange || !selection) return;
        selection.removeAllRanges();
        selection.addRange(savedRange);
    };

    surface.addEventListener('mouseup', captureSelection);
    surface.addEventListener('keyup', captureSelection);
    surface.addEventListener('blur', captureSelection);

    root.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => {
            captureSelection();
            event.preventDefault();
        });
        button.addEventListener('click', () => {
            const command = button.dataset.editorCommand;
            surface.focus();
            restoreSelection();

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

    const blockSelect = root.querySelector('[data-editor-block]');
    blockSelect?.addEventListener('focus', captureSelection);
    blockSelect?.addEventListener('mousedown', captureSelection);
    blockSelect?.addEventListener('change', (event) => {
        const block = event.currentTarget.value;
        if (!block) return;

        surface.focus();
        restoreSelection();
        document.execCommand('formatBlock', false, block);
        event.currentTarget.value = '';
        captureSelection();
        sync();
    });

    const insertImage = (url, alt = '') => {
        let resolvedUrl;

        try {
            resolvedUrl = new URL(url, window.location.origin);
        } catch {
            window.alert('آدرس تصویر معتبر نیست.');
            return;
        }

        const sameOrigin = resolvedUrl.origin === window.location.origin;
        const secureExternal = resolvedUrl.protocol === 'https:' && /^https:\/\//i.test(url);

        if (
            !['http:', 'https:'].includes(resolvedUrl.protocol)
            || (!sameOrigin && !secureExternal)
            || (sameOrigin && !/^https?:\/\//i.test(url) && !/^\/(storage|images)\//i.test(url))
        ) {
            window.alert('فقط HTTPS یا آدرس تصویر داخلی مجاز است.');
            return;
        }

        surface.focus();

        const selection = window.getSelection();
        let range = savedRange?.cloneRange() || null;

        if (!range && selection?.rangeCount && surface.contains(selection.anchorNode)) {
            range = selection.getRangeAt(0).cloneRange();
        }

        if (!range || !surface.contains(range.commonAncestorContainer)) {
            range = document.createRange();
            range.selectNodeContents(surface);
            range.collapse(false);
        }

        const image = document.createElement('img');
        image.src = resolvedUrl.href;
        image.alt = alt;
        image.loading = 'lazy';
        image.decoding = 'async';

        range.deleteContents();
        range.insertNode(image);
        range.setStartAfter(image);
        range.collapse(true);

        selection?.removeAllRanges();
        selection?.addRange(range);
        savedRange = null;
        sync();
    };

    surface.addEventListener('mouseup', captureSelection);
    surface.addEventListener('keyup', captureSelection);
    surface.addEventListener('blur', captureSelection);

    root.querySelectorAll('[data-editor-media-image]').forEach((button) => {
        button.addEventListener('pointerdown', captureSelection);
        button.addEventListener('mousedown', (event) => {
            captureSelection();
            event.preventDefault();
        });
        button.addEventListener('click', () => {
            insertImage(button.dataset.mediaUrl || '', button.dataset.mediaAlt || '');
        });
    });

    root.querySelector('[data-editor-insert-image]')?.addEventListener('pointerdown', captureSelection);
    root.querySelector('[data-editor-insert-image]')?.addEventListener('mousedown', (event) => {
        captureSelection();
        event.preventDefault();
    });
    root.querySelector('[data-editor-insert-image]')?.addEventListener('click', () => {
        const rawUrl = window.prompt('آدرس HTTPS یا مسیر تصویر در /storage/ یا /images/ را وارد کنید:');
        if (!rawUrl) return;

        const url = rawUrl.trim();
        const alt = window.prompt('متن جایگزین تصویر (Alt):') || '';
        insertImage(url, alt);
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


function normalizeText(value) {
    return String(value || '')
        .toLocaleLowerCase()
        .replace(/[ي]/g, 'ی')
        .replace(/[ك]/g, 'ک')
        .replace(/[^\\p{L}\\p{N}]+/gu, ' ')
        .trim()
        .replace(/\\s+/g, ' ');
}

function setupSeoAnalysis() {
    const panel = document.querySelector('[data-blog-seo-analysis]');
    if (!panel) return;

    const read = (selector) => document.querySelector(selector)?.value?.trim() || '';
    const titleInput = document.querySelector('[data-seo-title]');
    const metaTitleInput = document.querySelector('[data-seo-meta-title]');
    const metaDescriptionInput = document.querySelector('[data-seo-meta-description]');
    const excerptInput = document.querySelector('[data-seo-excerpt]');
    const keywordInput = document.querySelector('[data-seo-keyword]');
    const slugInput = document.querySelector('input[name="slug"]');
    const altInput = document.querySelector('input[name="cover_image_alt"]');
    const coverInput = document.querySelector('input[name="cover_image"]');
    const surface = document.querySelector('[data-editor-surface]');

    const update = () => {
        const title = read('[data-seo-meta-title]') || read('[data-seo-title]');
        const description = read('[data-seo-meta-description]') || read('[data-seo-excerpt]');
        const keyword = normalizeText(read('[data-seo-keyword]'));
        const slug = normalizeText(read('input[name="slug"]').replace(/-/g, ' '));
        const body = surface?.innerText || read('[data-editor-source]');
        const normalizedBody = normalizeText(body);
        const words = normalizedBody.split(' ').filter(Boolean);
        const firstWords = words.slice(0, 100).join(' ');
        const hasImage = Boolean(coverInput?.files?.length)
            || Boolean(document.querySelector('.admin-card img[alt]'));
        const hasHeadings = Boolean(surface?.querySelector('h2,h3,h4'));
        const checks = {
            title_length: title.length >= 25 && title.length <= 65,
            description_length: description.length >= 70 && description.length <= 165,
            focus_keyword: keyword.length > 0,
            keyword_title: keyword.length > 0 && normalizeText(title).includes(keyword),
            keyword_description: keyword.length > 0 && normalizeText(description).includes(keyword),
            keyword_slug: keyword.length > 0 && slug.includes(keyword),
            keyword_intro: keyword.length > 0 && firstWords.includes(keyword),
            content_depth: words.length >= 300,
            subheadings: hasHeadings,
            cover_image: hasImage,
            image_alt: read('input[name="cover_image_alt"]').length > 0,
            excerpt: read('[data-seo-excerpt]').length >= 40,
        };

        const checksList = [...panel.querySelectorAll('[data-seo-check]')];
        let passed = 0;
        checksList.forEach((item) => {
            const key = item.dataset.seoCheck;
            const success = Boolean(checks[key]);
            if (success) passed += 1;
            item.dataset.passed = success ? '1' : '0';
            const icon = item.querySelector('[data-check-icon]');
            if (icon) {
                icon.textContent = success ? '✓' : '!';
                icon.classList.toggle('text-emerald-600', success);
                icon.classList.toggle('text-amber-600', !success);
            }
        });

        const score = Math.round((passed / Math.max(1, checksList.length)) * 100);
        panel.querySelector('[data-seo-score]')?.replaceChildren(document.createTextNode(String(score)));
        const bar = panel.querySelector('[data-seo-score-bar]');
        if (bar) bar.style.width = score + '%';
        panel.querySelector('[data-seo-word-count]')?.replaceChildren(
            document.createTextNode(new Intl.NumberFormat('fa-IR').format(words.length))
        );

        panel.querySelector('[data-seo-preview-title]')?.replaceChildren(
            document.createTextNode(title || 'عنوان مقاله')
        );
        panel.querySelector('[data-seo-preview-description]')?.replaceChildren(
            document.createTextNode(description || 'توضیحات متا در این پیش‌نمایش نمایش داده می‌شود.')
        );
        document.querySelector('[data-seo-title-count]')?.replaceChildren(
            document.createTextNode(new Intl.NumberFormat('fa-IR').format(title.length) + ' / ۶۰')
        );
        document.querySelector('[data-seo-description-count]')?.replaceChildren(
            document.createTextNode(new Intl.NumberFormat('fa-IR').format(description.length) + ' / ۱۶۰')
        );
    };

    [
        titleInput, metaTitleInput, metaDescriptionInput, excerptInput,
        keywordInput, slugInput, altInput, coverInput,
    ].filter(Boolean).forEach((input) => {
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    });

    document.querySelector('[data-editor-surface]')?.addEventListener('input', update);
    document.querySelector('[data-editor-source]')?.addEventListener('input', update);
    update();
}

setupSeoAnalysis();
