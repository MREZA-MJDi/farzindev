@extends('admin.layouts.app')

@section('title', 'پیام‌های تماس')

@section('content')

    <div class="space-y-6">

        {{-- =========================================================
             HEADER
        ========================================================== --}}

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">
                    CONTACT MESSAGES
                </p>

                <h1 class="mt-2 text-2xl font-bold text-[var(--admin-text)]">
                    پیام‌های کاربران
                </h1>

                <p class="mt-2 text-sm text-[var(--admin-muted)]">
                    پیام‌های ارسال‌شده از فرم تماس سایت فرزین.
                </p>

            </div>

            <div class="flex items-center gap-2">

                <span class="rounded-xl bg-[var(--admin-surface)] px-3 py-2 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                    {{ number_format($messages->total()) }}
                    پیام
                </span>

            </div>

        </div>


        {{-- =========================================================
             FLASH MESSAGE
        ========================================================== --}}

        @if(session('success'))

            <div class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4">

                <p class="text-sm leading-7 text-[var(--admin-text)]">
                    {{ session('success') }}
                </p>

            </div>

        @endif


        {{-- =========================================================
             MESSAGE LIST
        ========================================================== --}}

        <div class="overflow-hidden rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-white)] shadow-[var(--admin-shadow-sm)]">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px] text-right">

                    {{-- Table Head --}}

                    <thead class="border-b border-[var(--admin-border)] bg-[var(--admin-surface)]">

                    <tr>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            وضعیت
                        </th>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            فرستنده
                        </th>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            موضوع
                        </th>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            تماس
                        </th>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            تاریخ
                        </th>

                        <th class="px-5 py-4 text-[10px] font-semibold text-[var(--admin-text-soft)]">
                            عملیات
                        </th>

                    </tr>

                    </thead>


                    {{-- Table Body --}}

                    <tbody class="divide-y divide-[var(--admin-border)]">

                    @forelse($messages as $message)

                        <tr class="transition-colors duration-200 hover:bg-[var(--admin-surface)]">

                            {{-- STATUS --}}

                            <td class="px-5 py-5">

                                @switch($message->status)

                                    @case('unread')

                                    <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1.5 text-[9px] font-bold text-red-700">
                                                جدید
                                            </span>

                                    @break

                                    @case('read')

                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-[9px] font-semibold text-gray-600">
                                                خوانده شده
                                            </span>

                                    @break

                                    @case('replied')

                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-[9px] font-bold text-emerald-700">
                                                پاسخ داده شده
                                            </span>

                                    @break

                                    @case('closed')

                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[9px] font-semibold text-slate-600">
                                                بسته شده
                                            </span>

                                    @break

                                    @default

                                    <span class="inline-flex items-center rounded-full bg-[var(--admin-surface)] px-3 py-1.5 text-[9px] font-semibold text-[var(--admin-text-soft)]">
                                                {{ $message->status }}
                                            </span>

                                @endswitch

                            </td>


                            {{-- SENDER --}}

                            <td class="px-5 py-5">

                                <div class="min-w-0">

                                    <p class="truncate text-xs font-semibold text-[var(--admin-text)]">
                                        {{ $message->name }}
                                    </p>

                                    @if($message->user)

                                        <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                            کاربر ثبت‌نام‌شده
                                        </p>

                                    @else

                                        <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                            مهمان
                                        </p>

                                    @endif

                                </div>

                            </td>


                            {{-- SUBJECT --}}

                            <td class="px-5 py-5">

                                    <span class="text-xs text-[var(--admin-text-soft)]">

                                        @switch($message->subject)

                                            @case('product')
                                            مشاوره درباره محصول
                                            @break

                                            @case('installment')
                                            شرایط خرید اقساطی
                                            @break

                                            @case('order')
                                            پیگیری سفارش
                                            @break

                                            @case('shipping')
                                            ارسال و تحویل
                                            @break

                                            @case('other')
                                            سایر
                                            @break

                                            @default
                                            {{ $message->subject }}

                                        @endswitch

                                    </span>

                            </td>


                            {{-- CONTACT --}}

                            <td class="px-5 py-5">

                                <div class="space-y-1">

                                    @if($message->phone)

                                        <p
                                            class="text-xs text-[var(--admin-text-soft)]"
                                            dir="ltr"
                                        >
                                            {{ $message->phone }}
                                        </p>

                                    @endif

                                    @if($message->email)

                                        <p
                                            class="max-w-48 truncate text-[9px] text-[var(--admin-muted)]"
                                            dir="ltr"
                                            title="{{ $message->email }}"
                                        >
                                            {{ $message->email }}
                                        </p>

                                    @endif

                                </div>

                            </td>


                            {{-- DATE --}}

                            <td class="px-5 py-5">

                                <div>

                                    <p class="text-[10px] font-medium text-[var(--admin-text-soft)]">
                                        {{ \App\Helpers\JalaliHelper::format($message->created_at, 'Y/m/d') }}
                                    </p>

                                    <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                        {{ $message->created_at->format('H:i') }}
                                    </p>

                                </div>

                            </td>


                            {{-- ACTIONS --}}

                            <td class="px-5 py-5">

                                <div class="flex items-center gap-2">

                                    <a
                                        href="{{ route('admin.contact-messages.show', $message) }}"
                                        class="inline-flex items-center justify-center rounded-xl bg-[var(--admin-text)] px-4 py-2.5 text-[10px] font-semibold text-white transition hover:bg-[var(--admin-accent)]"
                                    >
                                        مشاهده
                                    </a>

                                    @if($message->status === 'unread')

                                        <form
                                            action="{{ route('admin.contact-messages.read', $message) }}"
                                            method="POST"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                title="علامت‌گذاری به عنوان خوانده‌شده"
                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] text-[var(--admin-text-soft)] transition hover:border-[var(--admin-border-dark)] hover:bg-[var(--admin-surface)]"
                                            >
                                                ✓
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-20 text-center"
                            >

                                <div class="mx-auto max-w-sm">

                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--admin-surface)] text-[var(--admin-muted)]">
                                        ✉
                                    </div>

                                    <h2 class="mt-5 text-sm font-bold text-[var(--admin-text)]">
                                        هنوز پیامی دریافت نشده است
                                    </h2>

                                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                                        پیام‌هایی که کاربران از طریق فرم تماس سایت ارسال می‌کنند،
                                        در این قسمت نمایش داده خواهند شد.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}

            @if($messages->hasPages())

                <div class="border-t border-[var(--admin-border)] px-5 py-4">

                    {{ $messages->links() }}

                </div>

            @endif

        </div>

    </div>

@endsection
