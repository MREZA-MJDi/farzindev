<form
    action="{{ route('admin.media.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6"
>

    @csrf


    {{-- File --}}
    <div>

        <label
            for="file"
            class="mb-2 block text-xs font-medium text-[var(--admin-text)]"
        >
            فایل
        </label>

        <div
            class="rounded-3xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-bg)] p-8 text-center"
        >

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--admin-surface)] text-[var(--admin-muted)]">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-8 w-8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 16.5V3m0 0 4.5 4.5M12 3 7.5 7.5M5.25 21h13.5A2.25 2.25 0 0 0 21 18.75v-3A2.25 2.25 0 0 0 18.75 13.5h-1.5m-10.5 0h-1.5A2.25 2.25 0 0 0 3 15.75v3A2.25 2.25 0 0 0 5.25 21Z"
                    />
                </svg>

            </div>


            <h3 class="mt-4 text-sm font-semibold text-[var(--admin-text)]">
                فایل خود را انتخاب کنید
            </h3>

            <p class="mt-1 text-xs text-[var(--admin-text-soft)]">
                JPG، JPEG، PNG، WEBP، GIF و فایل‌های سند مجاز
            </p>

            <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                حداکثر حجم فایل: ۲۰ مگابایت
            </p>


            <label
                for="file"
                class="mt-5 inline-flex cursor-pointer items-center justify-center rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-5 py-3 text-xs font-medium text-[var(--admin-text)] transition hover:bg-[var(--admin-bg)]"
            >
                انتخاب فایل
            </label>


            <input
                id="file"
                name="file"
                type="file"
                class="hidden"
                accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip"
                required
            >


            <p
                id="selected-file-name"
                class="mt-3 hidden text-xs text-[var(--admin-accent)]"
            ></p>

        </div>


        @error('file')

        <p class="mt-2 text-xs text-red-500">
            {{ $message }}
        </p>

        @enderror

    </div>


    {{-- Alt --}}
    <div>

        <label
            for="alt"
            class="mb-2 block text-xs font-medium text-[var(--admin-text)]"
        >
            متن جایگزین
        </label>

        <input
            id="alt"
            name="alt"
            type="text"
            value="{{ old('alt') }}"
            placeholder="مثلاً نمای روبه‌روی هود مشکی فرزین"
            class="w-full rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-bg)] px-4 py-3 text-sm text-[var(--admin-text)] outline-none transition placeholder:text-[var(--admin-muted)] focus:border-[var(--admin-accent)]"
        >

        <p class="mt-2 text-[10px] text-[var(--admin-text-soft)]">
            برای تصاویر بهتر است متن توصیفی و مرتبط وارد شود.
        </p>

        @error('alt')

        <p class="mt-2 text-xs text-red-500">
            {{ $message }}
        </p>

        @enderror

    </div>


    {{-- Description --}}
    <div>

        <label
            for="description"
            class="mb-2 block text-xs font-medium text-[var(--admin-text)]"
        >
            توضیحات
        </label>

        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="توضیحات مربوط به این فایل..."
            class="w-full resize-none rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-bg)] px-4 py-3 text-sm text-[var(--admin-text)] outline-none transition placeholder:text-[var(--admin-muted)] focus:border-[var(--admin-accent)]"
        >{{ old('description') }}</textarea>

        @error('description')

        <p class="mt-2 text-xs text-red-500">
            {{ $message }}
        </p>

        @enderror

    </div>


    {{-- Actions --}}
    <div class="flex flex-col-reverse gap-3 border-t border-[var(--admin-border)] pt-5 sm:flex-row sm:justify-end">

        <a
            href="{{ route('admin.media.index') }}"
            class="inline-flex items-center justify-center rounded-2xl border border-[var(--admin-border)] px-5 py-3 text-xs font-medium text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-bg)] hover:text-[var(--admin-text)]"
        >
            انصراف
        </a>


        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-2xl bg-[var(--admin-accent)] px-6 py-3 text-xs font-medium text-white transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg"
        >
            آپلود رسانه
        </button>

    </div>

</form>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('selected-file-name');

        if (!fileInput || !fileName) {
            return;
        }

        fileInput.addEventListener('change', function () {

            if (!this.files.length) {
                fileName.textContent = '';
                fileName.classList.add('hidden');
                return;
            }

            fileName.textContent = 'فایل انتخاب‌شده: ' + this.files[0].name;
            fileName.classList.remove('hidden');

        });

    });
</script>
