document.addEventListener('DOMContentLoaded', function() {
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');

    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => handleExport('pdf'));
    }

    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', () => handleExport('excel'));
    }
});

async function handleExport(type) {
    const btn = type === 'pdf' ? document.getElementById('exportPdfBtn') : document.getElementById('exportExcelBtn');
    const url = btn.getAttribute('data-url');
    const filename = btn.getAttribute('data-filename');
    const totalRecords = parseInt(btn.getAttribute('data-total-records') || 0);

    if (totalRecords === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Ada Data',
            text: 'Tidak ada data transaksi pada periode ini untuk diekspor.',
            confirmButtonColor: '#0f172a'
        });
        return;
    }

    Swal.fire({
        title: 'Mempersiapkan Laporan',
        text: 'Mohon tunggu sebentar...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const params = new URLSearchParams(window.location.search);
        const response = await fetch(`${url}?${params.toString()}`);
        
        if (!response.ok) throw new Error('Gagal mengunduh file.');
        
        const blob = await response.blob();
        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = downloadUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(downloadUrl);
        
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Laporan telah berhasil diunduh.',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat mengekspor laporan.'
        });
    }
}
