@if($filterableAttributes->isNotEmpty())
<section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
    <x-layout.container>
        <form method="GET" action="{{ route('categories.show',$category->slug) }}" class="py-7">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div><p class="text-[10px] font-semibold tracking-[0.16em] text-[var(--livora-accent)]">مشخصات فنی</p><h2 class="mt-2 text-lg font-bold text-[var(--livora-ink)]">فیلتر {{ $category->name }}</h2></div>
                <div class="flex gap-2"><a href="{{ route('categories.show',$category->slug) }}" class="admin-btn admin-btn-secondary">پاک کردن</a><button type="submit" class="admin-btn admin-btn-primary">اعمال فیلتر</button></div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($filterableAttributes as $attribute)
                    <div class="rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">
                        <label class="mb-2 block text-xs font-semibold text-[var(--livora-text)]">{{ $attribute->label }} @if($attribute->unit) ({{ $attribute->unit }}) @endif</label>
                        @if($attribute->type === 'select')
                            <select name="attributes[{{ $attribute->id }}]" class="w-full rounded-lg border border-[var(--livora-border)] bg-white px-3 py-2.5 text-xs"><option value="">همه</option>@foreach($attribute->options as $option)<option value="{{ $option->id }}" @selected((string)request("attributes.{$attribute->id}")===(string)$option->id)>{{ $option->label }}</option>@endforeach</select>
                        @elseif($attribute->type === 'multiselect')
                            <div class="space-y-2">@foreach($attribute->options as $option)<label class="flex items-center gap-2 text-xs"><input type="checkbox" name="attributes[{{ $attribute->id }}][]" value="{{ $option->id }}" @checked(in_array((string)$option->id,array_map('strval',(array)request("attributes.{$attribute->id}",[])),true))>{{ $option->label }}</label>@endforeach</div>
                        @elseif($attribute->type === 'number')
                            <div class="grid grid-cols-2 gap-2"><input type="text" inputmode="decimal" name="attributes[{{ $attribute->id }}][min]" value="{{ request("attributes.{$attribute->id}.min") }}" placeholder="از" class="w-full rounded-lg border border-[var(--livora-border)] bg-white px-3 py-2.5 text-xs"><input type="text" inputmode="decimal" name="attributes[{{ $attribute->id }}][max]" value="{{ request("attributes.{$attribute->id}.max") }}" placeholder="تا" class="w-full rounded-lg border border-[var(--livora-border)] bg-white px-3 py-2.5 text-xs"></div>
                        @elseif($attribute->type === 'boolean')
                            <select name="attributes[{{ $attribute->id }}]" class="w-full rounded-lg border border-[var(--livora-border)] bg-white px-3 py-2.5 text-xs"><option value="">همه</option><option value="1" @selected(request("attributes.{$attribute->id}")==='1')>بله</option><option value="0" @selected(request("attributes.{$attribute->id}")==='0')>خیر</option></select>
                        @else
                            <input type="search" name="attributes[{{ $attribute->id }}]" value="{{ request("attributes.{$attribute->id}") }}" class="w-full rounded-lg border border-[var(--livora-border)] bg-white px-3 py-2.5 text-xs" placeholder="جستجو در مقدار">
                        @endif
                    </div>
                @endforeach
            </div>
        </form>
    </x-layout.container>
</section>
@endif
