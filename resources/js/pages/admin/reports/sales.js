document.addEventListener('DOMContentLoaded', () => {
    if (window.CustomDatePicker) window.CustomDatePicker.init();
    if (window.CustomSelect) window.CustomSelect.init();
    if (window.TablePagination) window.TablePagination.init();

    const data = window.REPORT_DATA || {};

    const ctx = document.getElementById('salesTrendChart');
    if (ctx && data.salesTrend) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.salesTrend.labels,
                datasets: [
                    {
                        label: 'Sparepart',
                        data: data.salesTrend.spareparts,
                        backgroundColor: '#0f172a',
                        borderRadius: 4,
                        barThickness: 30,
                    },
                    {
                        label: 'Layanan',
                        data: data.salesTrend.services,
                        backgroundColor: '#94a3b8',
                        borderRadius: 4,
                        barThickness: 30,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: { 
                            font: { size: 10 },
                            precision: 0,
                            stepSize: 1
                        }
                    },
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }
});
