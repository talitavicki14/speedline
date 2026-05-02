window.confirmDeletePhoto = function() {
    Swal.fire({
        title: 'Hapus Foto Profil?',
        text: "Foto profil Anda akan dikembalikan ke inisial nama.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#f1f5f9',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold',
            cancelButton: 'rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deletePhotoForm').submit();
        }
    });
};
