document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[data-initial-role]');
    if (!form) return;

    const roleSelect = form.querySelector('[name="role"]');
    const initialRole = form.dataset.initialRole;
    const internalRoles = ['admin', 'owner', 'mekanik', 'kasir'];

    form.addEventListener('submit', (e) => {
        const currentRole = roleSelect.value;
        
        if (initialRole === 'customer' && internalRoles.includes(currentRole) && !form.dataset.confirmed) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Konfirmasi Perubahan Peran',
                text: "Anda sedang mengubah peran pengguna ini dari Pelanggan menjadi Staff Internal. Ini akan memberikan akses ke area Admin. Lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold',
                    cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    });
});
