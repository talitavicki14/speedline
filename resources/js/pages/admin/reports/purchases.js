document.addEventListener('DOMContentLoaded', () => {
    if (window.CustomDatePicker) window.CustomDatePicker.init();
    if (window.CustomSelect) window.CustomSelect.init();
    if (window.TablePagination) window.TablePagination.init();

    const data = window.REPORT_DATA || {};

    const ctx = document.getElementById('purchaseTrendChart');
    if (ctx && data.trendData) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.trendData.map(t => t.month_label),
                datasets: [{
                    label: 'Pengeluaran',
                    data: data.trendData.map(t => t.total),
                    backgroundColor: '#0f172a',
                    borderRadius: 6,
                    barThickness: 30,
                    qtyData: data.trendData.map(t => t.qty)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                
                                const qty = context.dataset.qtyData[context.dataIndex];
                                return [label, `Total Stok: ${qty} pcs` ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { 
                            font: { size: 10 },
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }
});
