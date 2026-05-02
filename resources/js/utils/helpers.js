window.togglePassword = function (id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
};

window.previewImage = function(input, previewId, placeholderId = null) {
    const preview = document.getElementById(previewId);
    const placeholder = placeholderId ? document.getElementById(placeholderId) : null;

    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Bukan Gambar',
                text: 'File yang Anda pilih bukan gambar. Silakan pilih file jpeg, png, jpg, atau webp.',
                confirmButtonColor: '#0f172a'
            });
            input.value = '';
            return;
        }

        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran gambar maksimal adalah 2MB. Gambar Anda berukuran ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB.',
                confirmButtonColor: '#0f172a'
            });
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
};

window.showImageFull = function(src) {
    const img = document.getElementById('fullImageSrc');
    if (img) {
        img.src = src;
        openModal('fullImageModal');
    }
};

window.confirmDeletePhoto = function() {
    Swal.fire({
        title: 'Hapus Foto Profil?',
        text: "Foto profil akan dikembalikan ke inisial nama.",
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
            const form = document.getElementById('deletePhotoForm');
            if (form) form.submit();
        }
    });
};

window.closeFullImage = function() {
    closeModal('fullImageModal');
};

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', e => {
        const link = e.target.closest('a');
        if (link && link.href && !link.target && !link.href.includes('#') && !link.href.startsWith('javascript:')) {
            const progress = document.querySelector('.page-progress');
            if (progress) {
                progress.classList.remove('hidden');
                progress.style.animation = 'none';
                progress.offsetHeight;
                progress.style.animation = null;
            }
        }
    });

    const formatValue = (val) => {
        if (!val) return '';
        let str = val.toString().split(/[\.,](?=\d{2}$)/)[0];
        let numeric = str.replace(/\D/g, '');
        return numeric.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };

    const inputs = document.querySelectorAll('.input-currency');
    inputs.forEach(input => {
        if (input.value) input.value = formatValue(input.value);

        input.addEventListener('input', function() {
            this.value = formatValue(this.value);
        });
    });
});
