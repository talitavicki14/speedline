import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    let chartInstance = null;
    let chartState = {
        year: '',
        monthNum: '',
        isLine: false
    };

    const fullMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const initChart = () => {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const data = JSON.parse(canvas.dataset.revenue || '[]');
        
        chartState.period = canvas.dataset.period || 'monthly';
        chartState.isLine = chartState.period === 'daily';
        chartState.year = canvas.dataset.year;
        chartState.monthNum = canvas.dataset.month;

        createOrUpdateChart(ctx, labels, data);
    };

    const createOrUpdateChart = (ctx, labels, data) => {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const isLine = chartState.isLine;

        chartInstance = new Chart(ctx, {
            type: isLine ? 'line' : 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: data,
                    backgroundColor: isLine ? 'rgba(15, 23, 42, 0.05)' : '#0f172a',
                    hoverBackgroundColor: isLine ? '#0f172a' : 'rgba(15, 23, 42, 0.75)',
                    borderRadius: isLine ? 0 : 8,
                    borderColor: '#0f172a',
                    borderWidth: isLine ? 3 : 0,
                    fill: isLine,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: isLine ? 6 : 0,
                    pointBackgroundColor: '#0f172a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                layout: { padding: { left: 10, right: 10, top: 10, bottom: 0 } },
                plugins: { 
                    legend: { display: false }, 
                    tooltip: {
                        callbacks: { 
                            title: (items) => {
                                if (items.length === 0) return '';
                                const label = items[0].label;
                                if (chartState.isLine) {
                                    const mName = fullMonths[parseInt(chartState.monthNum) - 1];
                                    return `${label} ${mName} ${chartState.year}`;
                                } else {
                                    const mIdx = shortMonths.indexOf(label);
                                    const mName = mIdx !== -1 ? fullMonths[mIdx] : label;
                                    return `${mName} ${chartState.year}`;
                                }
                            },
                            label: ctx => 'Revenue: Rp ' + Number(ctx.raw).toLocaleString('id') 
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { family: 'Inter', size: 12 } } },
                    y: { 
                        grid: { color: '#f1f5f9' }, 
                        border: { display: false }, 
                        ticks: { 
                            color: '#94a3b8', 
                            font: { family: 'Inter', size: 11 }, 
                            padding: 10,
                            callback: v => 'Rp '+Number(v).toLocaleString('id') 
                        } 
                    }
                }
            }
        });
    };

    const updateChartData = async (period, year, month) => {
        const url = new URL(window.location.href);
        url.searchParams.set('period', period);
        url.searchParams.set('year', year);
        url.searchParams.set('month', month);

        try {
            const response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const resData = await response.json();

            chartState.year = resData.year;
            chartState.monthNum = resData.month;
            chartState.isLine = resData.period === 'daily';

            const chartTitle = document.getElementById('chartTitle');
            if (chartTitle) chartTitle.innerText = resData.chartTitle;

            const canvas = document.getElementById('revenueChart');
            if (canvas) {
                createOrUpdateChart(canvas.getContext('2d'), resData.labels, resData.revenue_data);
            }

            window.history.pushState({}, '', url.toString());

            const monthWrapper = document.getElementById('monthSelectorWrapper');
            if (monthWrapper && resData.monthOptions) {
                const customSelect = monthWrapper.querySelector('[data-custom-select]');
                if (customSelect) {
                    customSelect.setAttribute('data-options', JSON.stringify(resData.monthOptions));
                    const currentVal = resData.month.toString();
                    const activeMonth = resData.monthOptions.find(m => m.value === currentVal);
                    if (activeMonth && activeMonth.disabled) {
                        const firstEnabled = resData.monthOptions.find(m => !m.disabled);
                        if (firstEnabled) {
                            customSelect.dataset.value = firstEnabled.value;
                            updateChartData(period, year, firstEnabled.value);
                            return;
                        }
                    }
                    customSelect.dispatchEvent(new CustomEvent('custom-select:refresh', { bubbles: true }));
                }
            }

        } catch (error) {
            console.error('Failed to fetch chart data:', error);
        }
    };

    const setupFilters = () => {
        const filterContainer = document.getElementById('chartFilters');
        if (!filterContainer) return;

        document.addEventListener('custom-select:change', (e) => {
            if (!filterContainer.contains(e.target)) return;

            const period = filterContainer.querySelector('[data-name="period"] input[type="hidden"]').value;
            const year = filterContainer.querySelector('[data-name="year"] input[type="hidden"]').value;
            const month = filterContainer.querySelector('[data-name="month"] input[type="hidden"]').value;

            const monthWrapper = document.getElementById('monthSelectorWrapper');
            if (monthWrapper) {
                monthWrapper.classList.toggle('hidden', period !== 'daily');
            }

            updateChartData(period, year, month);
        });
    };

    initChart();
    setupFilters();
});
