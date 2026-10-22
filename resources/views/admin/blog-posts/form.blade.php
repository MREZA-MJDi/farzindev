@csrf
<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="space-y-6">
        <section class="admin-card space-y-5 p-5 sm:p-7">
            <div>
                <h2 class="text-base font-bold text-[var(--admin-text)]">محتوای مقاله</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">متن به‌صورت امن و با حفظ پاراگراف‌ها در خروجی نمایش داده می‌شود؛ HTML خام اجرا نمی‌شود.</p>
            </div>

            <label class="block text-sm text-[var(--admin-text)]">عنوان مقاله
                <input name="title" value="{{ old('title', $post->title) }}" maxlength="255" required class="admin-input mt-2 w-full">
                @error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">Slug انگلیسی
                <input name="slug" value="{{ old('slug', $post->slug) }}" dir="ltr" placeholder="choosing-the-right-kitchen-hood" class="admin-input mt-2 w-full text-left">
                <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">اگر خالی باشد، از عنوان انگلیسی ساخته می‌شود؛ برای عنوان فارسی بهتر است slug انگلیسی را دستی وارد کنید.</span>
                @error('slug')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">خلاصه / توضیح کارت
                <textarea name="excerpt" rows="3" maxlength="600" class="admin-input mt-2 w-full">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">متن مقاله
                <textarea name="body" rows="18" required class="admin-input mt-2 w-full leading-8">{{ old('body', $post->body) }}</textarea>
                @error('body')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </section>

        <section class="admin-card space-y-5 p-5 sm:p-7">
            <div>
                <h2 class="text-base font-bold text-[var(--admin-text)]">SEO اختصاصی مقاله</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">مقادیر اختصاصی هر مقاله نسبت به SEO پیش‌فرض سایت اولویت دارند.</p>
            </div>
            <label class="block text-sm text-[var(--admin-text)]">عنوان SEO
                <input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="255" class="admin-input mt-2 w-full">
                @error('meta_title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm text-[var(--admin-text)]">توضیحات متا
                <textarea name="meta_description" rows="3" maxlength="320" class="admin-input mt-2 w-full">{{ old('meta_description', $post->meta_description) }}</textarea>
                @error('meta_description')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="admin-card space-y-5 p-5">
            <h2 class="text-sm font-bold text-[var(--admin-text)]">انتشار</h2>
            <label class="block text-sm text-[var(--admin-text)]">وضعیت
                <select name="status" class="admin-select mt-2 w-full" required>
                    <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>پیش‌نویس</option>
                    <option value="published" @selected(old('status', $post->status ?? 'draft') === 'published')>انتشار / زمان‌بندی</option>
                </select>
                @error('status')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm text-[var(--admin-text)]">زمان انتشار (اختیاری)
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="admin-input mt-2 w-full" dir="ltr">
                <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">اگر وضعیت منتشرشده باشد و زمان خالی بماند، همین حالا منتشر می‌شود. تاریخ آینده، مقاله را تا آن زمان از سایت عمومی پنهان می‌کند.</span>
                @error('published_at')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </section>

        <section class="admin-card space-y-4 p-5">
            <h2 class="text-sm font-bold text-[var(--admin-text)]">تصویر شاخص</h2>
            @if($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="aspect-video w-full rounded-xl object-cover">
            @endif
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="admin-input w-full p-2">
            <p class="text-xs leading-6 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ حداقل ۸۰۰×۴۵۰؛ حداکثر ۸ مگابایت. اگر فایل جدید انتخاب نشود، تصویر قبلی حفظ می‌شود.</p>
            @error('cover_image')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
        </section>

        <button type="submit" class="admin-btn admin-btn-primary w-full">{{ isset($isEditing) && $isEditing ? 'ذخیره تغییرات' : 'ذخیره مقاله' }}</button>
        <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn admin-btn-ghost w-full">بازگشت به بلاگ</a>
    </aside>
</div>
