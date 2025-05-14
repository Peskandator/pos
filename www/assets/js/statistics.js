document.addEventListener('DOMContentLoaded', function () {
    // Sloupcovy graf trzeb za časové období
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        const labels = JSON.parse(salesChartEl.dataset.labels || '[]');
        const values = JSON.parse(salesChartEl.dataset.values || '[]');
        const start = new Date(salesChartEl.dataset.start);
        const end = new Date(salesChartEl.dataset.end);

        const ctx = salesChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tržby',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.75)',
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'category',
                        title: {
                            display: true,
                            text: 'Čas'
                        },
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 0,
                            maxTicksLimit: 14
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Tržby (Kč)'
                        },
                        ticks: {
                            callback: value => value.toLocaleString('cs-CZ') + ' Kč'
                        },
                        grid: {
                            color: '#eaeaea'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.formattedValue} Kč`
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Kolacovy graf podilu produktu
    const donutChartEl = document.getElementById('productDistributionChart');
    if (!donutChartEl) return;

    let labels = [], values = [];

    try {
        labels = JSON.parse(donutChartEl.dataset.labels || '[]');
        values = JSON.parse(donutChartEl.dataset.values || '[]');
    } catch (e) {
        console.error('Invalid chart data:', e);
        return;
    }

    if (labels.length === 0 || values.length === 0) {
        console.warn('No product data available for donut chart.');
        return;
    }


    const ctx = donutChartEl.getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#4e79a7', '#f28e2c', '#e15759',
                    '#76b7b2', '#59a14f', '#edc948',
                    '#b07aa1', '#ff9da7', '#9c755f'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percent = ((value / total) * 100).toFixed(1);
                            return `${context.label}: ${value} ks (${percent} %)`;
                        }
                    }
                }
            }
        }
    });

    // Kolacovy graf podilu trzeb podle produktu
    const revenueChartEl = document.getElementById('productRevenueChart');
    if (revenueChartEl) {
        try {
            const labels = JSON.parse(revenueChartEl.dataset.labels || '[]');
            const values = JSON.parse(revenueChartEl.dataset.values || '[]');

            const ctx = revenueChartEl.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#4e79a7', '#f28e2c', '#e15759',
                            '#76b7b2', '#59a14f', '#edc948',
                            '#b07aa1', '#ff9da7', '#9c755f'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;
                                    const percent = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value.toLocaleString('cs-CZ')} Kč (${percent} %)`;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Failed to load product revenue chart:', e);
        }
    }

});
