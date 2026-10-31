@csrf
<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm text-[var(--admin-text)]">نوع
        <select name="type" class="admin-select mt-2 w-full" required>
            <option value="category" @selected(old('type', $term->type) === 'category')>دسته‌بندی</option>
            <option value="tag" @selected(old('type', $term->type) === 'tag')>برچسب</option>
        </select>
        @error('type')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="block text-sm text-[var(--admin-text)]">نام
        <input name="name" value="{{ old('name', $term->name) }}" maxlength="160" required class="admin-input mt-2 w-full">
        @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
</div>

<label class="block text-sm text-[var(--admin-text)]">Slug انگلیسی
    <input name="slug" value="{{ old('slug', $term->slug) }}" maxlength="180" dir="ltr" placeholder="kitchen-hood" class="admin-input mt-2 w-full text-left">
    @error('slug')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
</label>

<label class="block text-sm text-[var(--admin-text)]">توضیحات
    <textarea name="description" rows="3" maxlength="2000" class="admin-input mt-2 w-full">{{ old('description', $term->description) }}</textarea>
</label>

<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm text-[var(--admin-text)]">عنوان SEO
        <input name="meta_title" value="{{ old('meta_title', $term->meta_title) }}" maxlength="255" class="admin-input mt-2 w-full">
    </label>
    <label class="block text-sm text-[var(--admin-text)]">توضیحات متا
        <textarea name="meta_description" rows="2" maxlength="320" class="admin-input mt-2 w-full">{{ old('meta_description', $term->meta_description) }}</textarea>
    </label>
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm text-[var(--admin-text)]">قانون ربات‌ها
        <select name="seo_robots" class="admin-select mt-2 w-full">
            @foreach (['inherit' => 'ارث‌بری از سایت', 'index,follow' => 'Index, Follow', 'noindex,follow' => 'Noindex, Follow', 'noindex,nofollow' => 'Noindex, Nofollow'] as $value => $label)
                <option value="{{ $value }}" @selected(old('seo_robots', $term->seo_robots ?: 'inherit') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="block text-sm text-[var(--admin-text)]">ترتیب نمایش
        <input type="number" min="0" max="100000" name="sort_order" value="{{ old('sort_order', $term->sort_order ?? 0) }}" class="admin-input mt-2 w-full">
    </label>
</div>

<label class="flex items-center gap-3 text-sm text-[var(--admin-text)]">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $term->is_active ?? true)) class="admin-checkbox h-4 w-4">
    نمایش عمومی
</label>
