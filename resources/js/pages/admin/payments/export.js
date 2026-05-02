document.addEventListener('DOMContentLoaded', () => {
    const exportForm = document.getElementById('exportForm');
    if (!exportForm) return;

    window.handleExport = async function(route) {
        const checkUrl = exportForm.dataset.checkUrl;
        const formData = new FormData(exportForm);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }

        try {
            const checkResponse = await fetch(`${checkUrl}?${params.toString()}`);
            const data = await checkResponse.json();

            if (data.count === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Kosong',
                    text: 'Tidak ada data pembayaran yang ditemukan untuk rentang tanggal dan filter tersebut.',
                    confirmButtonColor: '#0f172a',
                });
            } else {
                Swal.fire({
                    title: 'Menyiapkan Laporan...',
                    text: 'Mohon tunggu sebentar, sistem sedang mengolah data.',
                    allowOutsideClick: false,
                    padding: '3rem',
                    customClass: {
                        title: 'font-display text-xl pt-2',
                        htmlContainer: 'text-slate-500'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const response = await fetch(`${route}?${params.toString()}`);
                
                if (!response.ok) throw new Error('Download failed');

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                
                const contentDisposition = response.headers.get('Content-Disposition');
                let filename = route.includes('pdf') ? 'laporan_pembayaran.pdf' : 'laporan_pembayaran.xlsx';
                if (contentDisposition && contentDisposition.indexOf('filename=') !== -1) {
                    filename = contentDisposition.split('filename=')[1].replaceAll('"', '');
                }
                
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                if (window.closeModal) closeModal('exportModal');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Laporan Berhasil!',
                    text: 'Laporan Anda telah berhasil diunduh.',
                    confirmButtonColor: '#0f172a',
                });
            }
        } catch (error) {
            console.error('Export Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengunduh',
                text: 'Terjadi kesalahan saat memproses laporan. Silakan coba lagi.',
                confirmButtonColor: '#0f172a',
            });
        }
    };
});
