<footer class="mt-auto border-t border-[var(--admin-border)] bg-[var(--admin-white)]">

    <div class="flex flex-col gap-3 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">

        {{-- Brand --}}
        <div class="flex items-center gap-3">

            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--admin-text)] text-[9px] font-bold tracking-wider text-white">
                FZ
            </div>

            <div>

                <p class="text-[10px] font-semibold tracking-[0.16em] text-[var(--admin-text)]">
                    FARZIN ADMIN
                </p>

                <p class="mt-0.5 text-[9px] text-[var(--admin-muted)]">
                    مدیریت فروشگاه
                </p>

            </div>

        </div>


        {{-- System Status --}}
        <div class="flex flex-wrap items-center gap-4">

            <div class="flex items-center gap-2">

                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                <span class="text-[9px] text-[var(--admin-muted)]">
                    سیستم فعال است
                </span>

            </div>

            <span class="hidden h-4 w-px bg-[var(--admin-border)] sm:block"></span>

            <span class="text-[9px] text-[var(--admin-muted)]">
                Laravel {{ app()->version() }}
            </span>

            <span class="hidden h-4 w-px bg-[var(--admin-border)] sm:block"></span>

            <span class="text-[9px] text-[var(--admin-muted)]">
                © {{ \App\Helpers\JalaliHelper::format(now(), 'Y') }} فرزین
            </span>

        </div>

    </div>

</footer>
