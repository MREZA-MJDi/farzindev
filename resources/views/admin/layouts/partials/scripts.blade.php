{{-- =========================================================
     GLOBAL ADMIN SCRIPTS
========================================================= --}}

@vite([
'resources/js/app.js'
])


{{-- =========================================================
     FLASH MESSAGE AUTO DISMISS
========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {

        document
            .querySelectorAll('[data-auto-dismiss]')
            .forEach((element) => {

                const delay =
                    Number(
                        element.dataset.autoDismiss
                    ) || 4000;

                setTimeout(() => {

                    element.style.opacity = '0';

                    element.style.transform =
                        'translateY(-4px)';

                    element.style.transition =
                        'opacity 250ms ease, transform 250ms ease';

                    setTimeout(() => {
                        element.remove();
                    }, 250);

                }, delay);

            });

    });
</script>


{{-- =========================================================
     CONFIRM ACTIONS
========================================================= --}}

<script>
    document.addEventListener('click', (event) => {

        const element =
            event.target.closest('[data-confirm]');

        if (!element) {
            return;
        }

        const message =
            element.dataset.confirm;

        if (
            message
            && !window.confirm(message)
        ) {
            event.preventDefault();
        }

    });
</script>


{{-- =========================================================
     NUMBER FORMAT
========================================================= --}}

<script>
    window.FarzinAdmin = {

        formatNumber(value) {

            return new Intl.NumberFormat(
                'fa-IR'
            ).format(
                Number(value) || 0
            );

        },

        formatPrice(value) {

            return new Intl.NumberFormat(
                'fa-IR'
            ).format(
                Number(value) || 0
            ) + ' تومان';

        }

    };
</script>


{{-- =========================================================
     ESCAPE MODALS / DROPDOWNS
========================================================= --}}

<script>
    document.addEventListener('keydown', (event) => {

        if (event.key !== 'Escape') {
            return;
        }

        window.dispatchEvent(
            new CustomEvent('admin:escape')
        );

    });
</script>


{{-- =========================================================
     PAGE SPECIFIC SCRIPTS
========================================================= --}}

