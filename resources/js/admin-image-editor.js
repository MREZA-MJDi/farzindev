const inputs = Array.from(document.querySelectorAll('[data-image-editor-input]'));
const RATIOS = { '1:1': 1, '4:3': 4 / 3, '4:5': 4 / 5, '16:9': 16 / 9 };
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
    const panel = form && form.querySelector('[data-image-editor-panel]');
    const canvas = panel && panel.querySelector('[data-image-editor-canvas]');
    const ratioInput = panel && panel.querySelector('[data-image-editor-ratio]');
    const zoomInput = panel && panel.querySelector('[data-image-editor-zoom]');
    const zoomLabel = panel && panel.querySelector('[data-image-editor-zoom-label]');
    const status = panel && panel.querySelector('[data-image-editor-status]');
    const oldPreview = form && form.querySelector('[data-image-editor-existing-preview]');
    const previewWrap = oldPreview && (oldPreview.closest('.admin-image-preview') || oldPreview);
    const placeholder = form && form.querySelector('[data-image-editor-placeholder]');

    if (!form || !panel || !(canvas instanceof HTMLCanvasElement) || !ratioInput || !zoomInput) return;
    const ctx = canvas.getContext('2d', { alpha: false });
    if (!ctx) return;

    const original = {
        previewSrc: oldPreview ? oldPreview.getAttribute('src') : null,
        previewHidden: oldPreview ? oldPreview.classList.contains('hidden') : false,
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
        if (previewWrap) previewWrap.classList.remove('hidden');
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
        if (!file.type || file.type.indexOf('image/') !== 0) {
            clearEditor();
            say('این فایل تصویر نیست؛ بدون برش آپلود می‌شود.');
            return;
        }

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
                    rebuildStage();
                    renderPreview();
                    say('ابعاد اصلی: ' + image.naturalWidth.toLocaleString('fa-IR') + ' × ' + image.naturalHeight.toLocaleString('fa-IR') + ' پیکسل؛ کادر را تنظیم کنید.');
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

    form.addEventListener('submit', async (event) => {
        if (!state.file || state.readyToSubmit) return;
        event.preventDefault();
        if (state.processing) return;
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
            state.processing = false;
            say('تصویر برش خورد و آمادهٔ ذخیره است.');
            if (event.submitter) form.requestSubmit(event.submitter);
            else form.requestSubmit();
        } catch (error) {
            state.processing = false;
            say(error.message || 'برش تصویر انجام نشد؛ دوباره تلاش کنید.');
        }
    });
}

inputs.forEach(initImageEditor);
