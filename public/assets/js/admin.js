'use strict';

window.initAdminCharts = function (data) {
    if (typeof Chart === 'undefined' || !data) return;

    // Green palette
    var c = {
        darkest:  '#051F20',
        dark:     '#0B2B26',
        midDark:  '#163832',
        primary:  '#235347',
        sage:     '#8EB69B',
        mint:     '#DAF1DE',
        border:   '#c8e2cf',
    };

    Chart.defaults.font.family  = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size    = 12;
    Chart.defaults.color        = '#5a7a6e';
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.plugins.legend.labels.usePointStyle  = true;
    Chart.defaults.plugins.legend.labels.pointStyleWidth = 8;

    // Revenue line chart
    var revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels:   data.revenue?.labels  || [],
                datasets: [{
                    label:           'Revenue (GH₵)',
                    data:            data.revenue?.values || [],
                    borderColor:     c.primary,
                    backgroundColor: 'rgba(35,83,71,.08)',
                    tension:         .4,
                    fill:            true,
                    pointRadius:     4,
                    pointBackgroundColor: c.primary,
                    borderWidth:     2,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { color: c.border } },
                    y: { grid: { color: '#e8f5eb' }, border: { dash: [4,4], color: 'transparent' } },
                },
            },
        });
    }

    // Properties bar chart
    var propCanvas = document.getElementById('propertiesChart');
    if (propCanvas) {
        new Chart(propCanvas, {
            type: 'bar',
            data: {
                labels:   data.properties?.labels  || [],
                datasets: [{
                    label:           'New Properties',
                    data:            data.properties?.values || [],
                    backgroundColor: c.primary,
                    borderRadius:    4,
                    borderSkipped:   false,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { color: c.border } },
                    y: { grid: { color: '#e8f5eb' }, border: { dash: [4,4], color: 'transparent' } },
                },
            },
        });
    }

    // Status doughnut
    var statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels:   data.statuses?.labels || [],
                datasets: [{
                    data:            data.statuses?.values || [],
                    backgroundColor: [c.primary, c.midDark, c.sage, c.mint, c.border],
                    borderColor:     '#fff',
                    borderWidth:     3,
                }],
            },
            options: {
                responsive: true,
                cutout: '66%',
                plugins: {
                    legend: { labels: { color: '#5a7a6e' } },
                },
            },
        });
    }
};

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.adminChartData !== 'undefined') {
        window.initAdminCharts(window.adminChartData);
    }
});
