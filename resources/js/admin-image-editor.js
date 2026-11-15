const inputs = Array.from(document.querySelectorAll('[data-image-editor-input]'));
const formEditorRegistry = new WeakMap();

function registerFormEditor(form, editor) {
    let registry = formEditorRegistry.get(form);

    if (!registry) {
        registry = { editors: [], processing: false };
        formEditorRegistry.set(form, registry);

        form.addEventListener('submit', async (event) => {
            const pendingEditors = registry.editors.filter((item) => item.hasPendingFile());
            if (pendingEditors.length === 0) return;

            event.preventDefault();
            if (registry.processing) return;

            registry.processing = true;
            try {
                // Process every selected crop sequentially. Multiple editors on
                // one form must never race or trigger duplicate form submissions.
                for (const editorItem of pendingEditors) {
                    await editorItem.prepare();
                }

                form.requestSubmit(event.submitter || undefined);
            } catch (error) {
                console.error('Unable to prepare page content images.', error);
                window.dispatchEvent(new CustomEvent('page-content-save-error', {
                    detail: { message: error.message || 'برش تصویر انجام نشد؛ دوباره تلاش کن.' },
                }));
            } finally {
                registry.processing = false;
            }
        });
    }

    registry.editors.push(editor);
}
const RATIOS = { '1:1': 1, '4:3': 4 / 3, '4:5': 4 / 5, '16:9': 16 / 9, '16:10': 16 / 10 };
const MAX_EDGE = 3200;

function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function imageBlob(canvas) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) resolve(blob);
            else reject(new Error('مرورگر نتوانست تصویر برش‌خورده را آماده کند.'));
        }, 'image/webp', 0.94);
    });
}

function initImageEditor(input) {
    const form = input.closest('form');
    const targetedPanel = form && input.id ? form.querySelector('[data-image-editor-panel-for="' + input.id + '"]') : null;
    const panel = targetedPanel || (form && form.querySelector('[data-image-editor-panel]'));
    const canvas = panel && panel.querySelector('[data-image-editor-canvas]');
    const ratioInput = panel && panel.querySelector('[data-image-editor-ratio]');
    const zoomInput = panel && panel.querySelector('[data-image-editor-zoom]');
    const zoomLabel = panel && panel.querySelector('[data-image-editor-zoom-label]');
    const status = panel && panel.querySelector('[data-image-editor-status]');
    const targetedPreview = form && input.id ? form.querySelector('[data-image-editor-existing-preview-for="' + input.id + '"]') : null;
    const oldPreview = targetedPreview || (form && form.querySelector('[data-image-editor-existing-preview]'));
    const previewWrap = oldPreview && (oldPreview.closest('.admin-image-preview') || oldPreview);
    const targetedPlaceholder = form && input.id ? form.querySelector('[data-image-editor-placeholder-for="' + input.id + '"]') : null;
    const placeholder = targetedPlaceholder || (form && form.querySelector('[data-image-editor-placeholder]'));

    if (!form || !panel || !(canvas instanceof HTMLCanvasElement) || !ratioInput || !zoomInput) return;
    const ctx = canvas.getContext('2d', { alpha: false });
    if (!ctx) return;
    input.dataset.imageEditorReady = 'true';

    const original = {
        previewSrc: oldPreview ? oldPreview.getAttribute('src') : null,
        previewHidden: oldPreview ? oldPreview.classList.contains('hidden') : false,
        previewWrapHidden: previewWrap ? previewWrap.classList.contains('hidden') : false,
        placeholderHidden: placeholder ? placeholder.classList.contains('hidden') : true,
    };
    const state = {
        file: null,
        image: null,
        stage: null,
        crop: null,
        loadPromise: null,
        rotation: 0,
        zoom: 1,
        panX: 0,
        panY: 0,
        pointer: null,
        processing: false,
        readyToSubmit: false,
    };

    const say = (message) => { if (status) status.textContent = message; };
    const ratio = () => RATIOS[ratioInput.value] || RATIOS['4:3'];
    const minimumWidth = () => Math.max(1, Number(input.dataset.imageEditorMinWidth) || 800);
    const minimumHeight = () => Math.max(1, Number(input.dataset.imageEditorMinHeight) || 600);
    const maximumBytes = () => Math.max(1, Number(input.dataset.imageEditorMaxSize) || 20 * 1024 * 1024);
    const maximumSourcePixels = () => Math.max(1, Number(input.dataset.imageEditorMaxSourcePixels) || 24000000);

    function rebuildStage() {
        if (!state.image) return;
        const rotation = ((state.rotation % 360) + 360) % 360;
        const swap = rotation === 90 || rotation === 270;
        const stage = document.createElement('canvas');
        stage.width = swap ? state.image.naturalHeight : state.image.naturalWidth;
        stage.height = swap ? state.image.naturalWidth : state.image.naturalHeight;
        const stageContext = stage.getContext('2d');
        if (!stageContext) throw new Error('ساخت پیش‌نمایش تصویر امکان‌پذیر نشد.');

        if (rotation === 90) {
            stageContext.translate(stage.width, 0);
            stageContext.rotate(Math.PI / 2);
        } else if (rotation === 180) {
            stageContext.translate(stage.width, stage.height);
            stageContext.rotate(Math.PI);
        } else if (rotation === 270) {
            stageContext.translate(0, stage.height);
            stageContext.rotate(-Math.PI / 2);
        }

        stageContext.drawImage(state.image, 0, 0);
        state.stage = stage;
    }

    function getCrop() {
        if (!state.stage) return null;
        const stage = state.stage;
        const targetRatio = ratio();
        let width = stage.width;
        let height = width / targetRatio;
        if (height > stage.height) {
            height = stage.height;
            width = height * targetRatio;
        }
        width /= state.zoom;
        height /= state.zoom;
        const maxX = Math.max(0, (stage.width - width) / 2);
        const maxY = Math.max(0, (stage.height - height) / 2);
        state.panX = clamp(state.panX, -maxX, maxX);
        state.panY = clamp(state.panY, -maxY, maxY);
        return {
            x: clamp((stage.width - width) / 2 + state.panX, 0, stage.width - width),
            y: clamp((stage.height - height) / 2 + state.panY, 0, stage.height - height),
            width: width,
            height: height,
        };
    }

    function renderPreview() {
        if (!state.stage) return;
        const targetRatio = ratio();
        canvas.width = 960;
        canvas.height = Math.max(1, Math.round(960 / targetRatio));
        canvas.style.aspectRatio = String(targetRatio);
        state.crop = getCrop();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#f0f2f4';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (state.crop) {
            ctx.drawImage(state.stage, state.crop.x, state.crop.y, state.crop.width, state.crop.height, 0, 0, canvas.width, canvas.height);
        }
        if (zoomLabel) zoomLabel.textContent = state.zoom.toLocaleString('fa-IR', { maximumFractionDigits: 2 }) + '×';

        if (state.crop && status) {
            const outputScale = Math.min(1, MAX_EDGE / Math.max(state.crop.width, state.crop.height));
            const outputWidth = Math.max(1, Math.round(state.crop.width * outputScale));
            const outputHeight = Math.max(1, Math.round(state.crop.height * outputScale));
            const minWidth = minimumWidth();
            const minHeight = minimumHeight();

            if (outputWidth < minWidth || outputHeight < minHeight) {
                say(
                    'وضوح کادر انتخاب‌شده کافی نیست. خروجی فعلی ' +
                    outputWidth.toLocaleString('fa-IR') + '×' + outputHeight.toLocaleString('fa-IR') +
                    ' پیکسل است؛ حداقل موردنیاز ' +
                    minWidth.toLocaleString('fa-IR') + '×' + minHeight.toLocaleString('fa-IR') +
                    ' پیکسل است. زوم را کمتر کنید یا تصویر بزرگ‌تری انتخاب کنید.'
                );
            } else if (status.textContent.includes('حداقل موردنیاز')) {
                say('وضوح خروجی برش مناسب است؛ می‌توانید تصویر را ذخیره کنید.');
            }
        }
    }

    function restorePreview() {
        if (oldPreview) {
            if (original.previewSrc) oldPreview.setAttribute('src', original.previewSrc);
            else oldPreview.removeAttribute('src');
            oldPreview.classList.toggle('hidden', original.previewHidden);
        }
        if (previewWrap) previewWrap.classList.toggle('hidden', original.previewWrapHidden);
        if (placeholder) {
            placeholder.classList.toggle('hidden', original.placeholderHidden);
            placeholder.classList.toggle('flex', !original.placeholderHidden);
        }
    }

    function clearEditor() {
        state.file = null;
        state.image = null;
        state.stage = null;
        state.crop = null;
        state.loadPromise = null;
        state.rotation = 0;
        state.zoom = 1;
        state.panX = 0;
        state.panY = 0;
        state.readyToSubmit = false;
        panel.hidden = true;
        restorePreview();
        say('فایل اصلی بدون تغییر باقی می‌ماند.');
    }

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        state.readyToSubmit = false;
        if (!file) {
            clearEditor();
            return;
        }
        if (!file.type || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            clearEditor();
            say('فقط تصویر JPG، PNG یا WEBP قابل انتخاب است.');
            input.setCustomValidity('فرمت تصویر باید JPG، PNG یا WEBP باشد.');
            input.reportValidity();
            return;
        }
        if (file.size > maximumBytes()) {
            clearEditor();
            say('حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.');
            input.setCustomValidity('حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.');
            input.reportValidity();
            return;
        }
        input.setCustomValidity('');

        state.file = file;
        state.image = null;
        state.stage = null;
        state.crop = null;
        state.rotation = 0;
        state.zoom = 1;
        state.panX = 0;
        state.panY = 0;
        zoomInput.value = '1';
        ratioInput.value = input.dataset.imageEditorDefaultRatio || '4:3';
        if (previewWrap) previewWrap.classList.add('hidden');
        if (placeholder) placeholder.classList.add('hidden');
        panel.hidden = false;
        say('در حال آماده‌سازی تصویر…');

        const objectUrl = URL.createObjectURL(file);
        const image = new Image();
        state.image = image;
        state.loadPromise = new Promise((resolve, reject) => {
            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                try {
                    if (image.naturalWidth < minimumWidth() || image.naturalHeight < minimumHeight()) {
                        throw new Error('ابعاد تصویر اصلی باید حداقل ' + minimumWidth() + '×' + minimumHeight() + ' پیکسل باشد.');
                    }
                    if ((image.naturalWidth * image.naturalHeight) > maximumSourcePixels()) {
                        throw new Error('ابعاد فایل بیش از حد مجاز است؛ تصویر کوچک‌تری انتخاب کنید.');
                    }
                    rebuildStage();
                    say('ابعاد اصلی: ' + image.naturalWidth.toLocaleString('fa-IR') + ' × ' + image.naturalHeight.toLocaleString('fa-IR') + ' پیکسل؛ کادر را تنظیم کنید.');
                    renderPreview();
                    resolve();
                } catch (error) {
                    reject(error);
                }
            };
            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('خواندن تصویر ممکن نشد؛ فرمت فایل را بررسی کنید.'));
            };
        });
        image.src = objectUrl;
        state.loadPromise.catch((error) => say(error.message || 'پیش‌نمایش تصویر آماده نشد.'));
    });

    ratioInput.addEventListener('change', () => {
        state.panX = 0;
        state.panY = 0;
        renderPreview();
    });
    zoomInput.addEventListener('input', () => {
        state.zoom = Number(zoomInput.value) || 1;
        renderPreview();
    });
    panel.querySelectorAll('[data-image-editor-rotate]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!state.image) return;
            state.rotation = (state.rotation + Number(button.dataset.imageEditorRotate || 90) + 360) % 360;
            state.panX = 0;
            state.panY = 0;
            try { rebuildStage(); renderPreview(); }
            catch (error) { say(error.message || 'چرخش تصویر انجام نشد.'); }
        });
    });
    panel.querySelector('[data-image-editor-reset]')?.addEventListener('click', () => {
        if (!state.image) return;
        state.rotation = 0;
        state.zoom = 1;
        state.panX = 0;
        state.panY = 0;
        zoomInput.value = '1';
        ratioInput.value = input.dataset.imageEditorDefaultRatio || '4:3';
        rebuildStage();
        renderPreview();
    });

    canvas.addEventListener('pointerdown', (event) => {
        if (!state.stage) return;
        state.pointer = { id: event.pointerId, x: event.clientX, y: event.clientY };
        canvas.setPointerCapture(event.pointerId);
        canvas.style.cursor = 'grabbing';
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!state.pointer || state.pointer.id !== event.pointerId || !state.crop) return;
        const bounds = canvas.getBoundingClientRect();
        const dx = event.clientX - state.pointer.x;
        const dy = event.clientY - state.pointer.y;
        state.pointer.x = event.clientX;
        state.pointer.y = event.clientY;
        state.panX -= dx * state.crop.width / Math.max(1, bounds.width);
        state.panY -= dy * state.crop.height / Math.max(1, bounds.height);
        renderPreview();
    });
    const stopDragging = (event) => {
        if (state.pointer && state.pointer.id === event.pointerId) state.pointer = null;
        canvas.style.cursor = 'grab';
    };
    canvas.addEventListener('pointerup', stopDragging);
    canvas.addEventListener('pointercancel', stopDragging);
    canvas.addEventListener('lostpointercapture', stopDragging);

    registerFormEditor(form, {
        hasPendingFile: () => Boolean(state.file) && !state.readyToSubmit,
        prepare: async () => {
            if (!state.file || state.readyToSubmit) return;
            state.processing = true;
            say('در حال برش تصویر با حفظ وضوح…');

            try {
                if (state.loadPromise) await state.loadPromise;
                if (!state.stage || !state.crop) throw new Error('پیش‌نمایش هنوز آماده نیست.');
                const crop = state.crop;
                const scale = Math.min(1, MAX_EDGE / Math.max(crop.width, crop.height));
                const output = document.createElement('canvas');
                output.width = Math.max(1, Math.round(crop.width * scale));
                output.height = Math.max(1, Math.round(crop.height * scale));

                if (output.width < minimumWidth() || output.height < minimumHeight()) {
                    throw new Error(
                        'وضوح تصویر برش‌خورده کافی نیست. حداقل ابعاد موردنیاز ' +
                        minimumWidth().toLocaleString('fa-IR') + '×' +
                        minimumHeight().toLocaleString('fa-IR') +
                        ' پیکسل است؛ تصویر بزرگ‌تری انتخاب کنید یا کادر را تغییر دهید.'
                    );
                }

                const outputContext = output.getContext('2d');
                if (!outputContext) throw new Error('ساخت خروجی برش ممکن نشد.');
                outputContext.drawImage(state.stage, crop.x, crop.y, crop.width, crop.height, 0, 0, output.width, output.height);

                const blob = await imageBlob(output);
                const type = blob.type || 'image/png';
                const extension = type === 'image/webp' ? 'webp' : (type === 'image/jpeg' ? 'jpg' : 'png');
                const name = (state.file.name.replace(/\.[^.]+$/, '') || 'farzin-image') + '-cropped.' + extension;
                const croppedFile = new File([blob], name, { type: type, lastModified: Date.now() });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                input.files = transfer.files;
                state.file = null;
                state.readyToSubmit = true;
                say('تصویر برش خورد و آمادهٔ ذخیره است.');
            } catch (error) {
                say(error.message || 'برش تصویر انجام نشد؛ دوباره تلاش کنید.');
                throw error;
            } finally {
                state.processing = false;
            }
        },
    });
}

inputs.forEach(initImageEditor);


// Lightweight multi-image previews with client-side limits matching the server rules.
document.querySelectorAll('[data-image-preview-input]').forEach((input) => {
    const preview = document.querySelector(input.dataset.imagePreviewTarget || '');
    const maxFiles = Math.max(1, Number(input.dataset.imagePreviewMaxFiles) || 10);
    const maxBytes = Math.max(1, Number(input.dataset.imagePreviewMaxSize) || 20 * 1024 * 1024);
    const minWidth = Math.max(1, Number(input.dataset.imagePreviewMinWidth) || 800);
    const minHeight = Math.max(1, Number(input.dataset.imagePreviewMinHeight) || 600);
    let objectUrls = [];

    const clearPreview = () => {
        objectUrls.forEach((url) => URL.revokeObjectURL(url));
        objectUrls = [];
        if (preview) preview.replaceChildren();
    };

    input.addEventListener('change', async () => {
        clearPreview();
        input.setCustomValidity('');

        const files = Array.from(input.files || []);
        if (!files.length) return;

        let error = '';
        if (files.length > maxFiles) {
            error = 'حداکثر ' + maxFiles.toLocaleString('fa-IR') + ' تصویر را در هر بار انتخاب کنید.';
        }

        for (const file of files) {
            if (error) break;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                error = 'فقط فایل‌های JPG، PNG و WEBP مجاز هستند.';
                break;
            }
            if (file.size > maxBytes) {
                error = 'حجم هر تصویر نباید بیشتر از ' + Math.round(maxBytes / 1024 / 1024) + ' مگابایت باشد.';
                break;
            }

            const objectUrl = URL.createObjectURL(file);
            objectUrls.push(objectUrl);
            const dimensions = await new Promise((resolve) => {
                const image = new Image();
                image.onload = () => resolve({ width: image.naturalWidth, height: image.naturalHeight });
                image.onerror = () => resolve(null);
                image.src = objectUrl;
            });

            if (!dimensions) {
                error = 'یکی از تصاویر قابل خواندن نیست؛ فایل دیگری انتخاب کنید.';
                break;
            }
            if (dimensions.width < minWidth || dimensions.height < minHeight) {
                error = 'ابعاد هر تصویر باید حداقل ' + minWidth + '×' + minHeight + ' پیکسل باشد.';
                break;
            }

            if (preview) {
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]';
                const image = document.createElement('img');
                image.src = objectUrl;
                image.alt = file.name;
                image.loading = 'lazy';
                image.className = 'aspect-[4/3] w-full object-cover';
                const meta = document.createElement('p');
                meta.className = 'truncate p-2 text-[10px] text-[var(--admin-muted)]';
                meta.textContent = file.name + ' · ' + dimensions.width + '×' + dimensions.height + ' · ' + (file.size / 1024 / 1024).toFixed(1) + ' MB';
                card.append(image, meta);
                preview.append(card);
            }
        }

        if (error) {
            clearPreview();
            input.setCustomValidity(error);
            input.reportValidity();
            return;
        }

        input.setCustomValidity('');
    });

    input.form?.addEventListener('submit', (event) => {
        if (!input.checkValidity()) {
            event.preventDefault();
            input.reportValidity();
        }
    });
});
