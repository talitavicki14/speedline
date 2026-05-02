document.addEventListener('DOMContentLoaded', () => {
    if (window.CustomDatePicker) window.CustomDatePicker.init();
    if (window.CustomSelect) window.CustomSelect.init();
    if (window.TablePagination) window.TablePagination.init();

    const data = window.REPORT_DATA || {};

    const ctxRevenue = document.getElementById('revenueTrendChart');
    if (ctxRevenue && data.revenueTrend) {
        new Chart(ctxRevenue.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.revenueTrend.labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: data.revenueTrend.values,
                    backgroundColor: '#0f172a',
                    borderRadius: 6,
                    barThickness: 30,
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
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], drawBorder: false },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
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

    const getTooltipCallback = () => ({
        label: function(context) {
            const label = context.label || '';
            const value = parseFloat(context.raw) || 0;
            const datasetData = context.dataset.data;
            const total = datasetData.reduce((a, b) => a + parseFloat(b), 0);
            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
            
            return `${label}: ${value} unit (${percentage}%)`;
        }
    });

    const ctxServices = document.getElementById('servicesPieChart');
    if (ctxServices && data.topServices) {
        new Chart(ctxServices.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.topServices.labels,
                datasets: [{
                    data: data.topServices.values,
                    backgroundColor: ['#0f172a', '#1e293b', '#334155', '#475569', '#64748b'],
                    borderWidth: 0,
                    cutout: '65%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true, font: { size: 10 } } },
                    tooltip: { callbacks: getTooltipCallback() }
                }
            }
        });
    }

    const ctxSpareparts = document.getElementById('sparepartsPieChart');
    if (ctxSpareparts && data.topSpareparts) {
        new Chart(ctxSpareparts.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.topSpareparts.labels,
                datasets: [{
                    data: data.topSpareparts.values,
                    backgroundColor: ['#0f172a', '#334155', '#475569', '#64748b', '#94a3b8'],
                    borderWidth: 0,
                    cutout: '65%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 8, usePointStyle: true, font: { size: 10 } } },
                    tooltip: { callbacks: getTooltipCallback() }
                }
            }
        });
    }
});
