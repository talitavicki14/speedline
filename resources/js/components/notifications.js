const Notify = {
    /**
     * Show a simple toast or modal notification.
     * @param {string} icon - 'success', 'error', 'warning', 'info'
     * @param {string} title - The title of the alert
     * @param {string} text - The message body
     */
    show(icon, title, text) {
        if (!window.Swal) {
            console.error('SweetAlert2 (Swal) is not loaded.');
            console.info(`${title}: ${text}`);
            return;
        }

        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'bg-slate-900 text-white px-6 py-2 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors outline-none'
            },
            buttonsStyling: false
        });
    },

    success(text, title = 'Berhasil') { this.show('success', title, text); },
    error(text, title = 'Kesalahan') { this.show('error', title, text); },
    warning(text, title = 'Peringatan') { this.show('warning', title, text); },
    info(text, title = 'Informasi') { this.show('info', title, text); },

    /**
     * Show a confirmation dialog.
     * @param {string} text - The question or message
     * @param {function} onConfirm - Callback when user clicks 'Yes'
     * @param {string} title - The dialog title
     */
    confirm(text, onConfirm, title = 'Konfirmasi') {
        if (!window.Swal) {
            console.error('SweetAlert2 (Swal) is not loaded for confirmation.');
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Tidak, batalkan',
            customClass: {
                confirmButton: 'bg-slate-900 text-white px-6 py-2 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors outline-none mr-2',
                cancelButton: 'bg-slate-100 text-slate-600 px-6 py-2 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors outline-none ml-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    }
};

window.Notify = Notify;

document.addEventListener('DOMContentLoaded', () => {
    const flash = {
        success: document.body.dataset.flashSuccess,
        error: document.body.dataset.flashError,
        warning: document.body.dataset.flashWarning,
        info: document.body.dataset.flashInfo,
        verified: document.body.dataset.flashVerified === 'true'
    };

    if (flash.success) Notify.success(flash.success);
    if (flash.error) Notify.error(flash.error);
    if (flash.warning) Notify.warning(flash.warning);
    if (flash.info) Notify.info(flash.info);
    if (flash.verified) {
        Notify.show('success', 'Akun Terverifikasi!', 'Selamat! Email Anda berhasil diverifikasi. Sekarang Anda dapat menggunakan seluruh layanan Speedline.');
        localStorage.removeItem('speedline_auth_resend_cooldown');
    }

    document.addEventListener('submit', (e) => {
        const form = e.target;
        const confirmMsg = form.getAttribute('data-confirm');

        if (confirmMsg && !form.dataset.confirmed) {
            e.preventDefault();
            Notify.confirm(confirmMsg, () => {
                form.dataset.confirmed = 'true';
                form.submit();
            });
        }
    });

    document.addEventListener('click', (e) => {
        const target = e.target.closest('[data-confirm-click]');
        if (target && !target.dataset.confirmed) {
            e.preventDefault();
            const confirmMsg = target.getAttribute('data-confirm-click');

            Notify.confirm(confirmMsg, () => {
                target.dataset.confirmed = 'true';
                if (target.tagName === 'A') {
                    window.location.href = target.href;
                } else if (target.type === 'submit') {
                    target.closest('form').submit();
                } else {
                    target.click();
                }
            });
        }
    });
});
