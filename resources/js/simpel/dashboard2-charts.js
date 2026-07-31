const Chart = require('chart.js/auto');

if (!window.Dashboard2Data) return;

const teal = '#099a97';
const tealLight = 'rgba(9, 154, 151, 0.16)';
const teal80 = 'rgba(9, 154, 151, 0.80)';
const gray400 = '#adb5bd';
const gray600 = '#868e96';
const green = '#2fb344';
const yellow = '#f76707';
const red = '#e03131';
const blue = '#206bc4';

const sparkDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    scales: { x: { display: false }, y: { display: false } },
    elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } },
    animation: false,
};

// 1. chart-visitors — line sparkline with 2 series
const labels30 = window.Dashboard2Data.dailyLabels;
const counts30 = window.Dashboard2Data.dailyCounts;

// Simulate "last month" with slight variation
const lastMonth = counts30.map(v => Math.max(0, v + Math.floor(Math.random() * 6) - 3));

new Chart(document.getElementById('chart-visitors'), {
    type: 'line',
    data: {
        labels: labels30,
        datasets: [
            { data: counts30, borderColor: teal, borderWidth: 2, fill: false, tension: 0.4 },
            { data: lastMonth, borderColor: gray400, borderWidth: 1, borderDash: [3, 3], fill: false, tension: 0.4 },
        ]
    },
    options: {
        ...sparkDefaults,
        elements: { ...sparkDefaults.elements, line: { borderWidth: 2, tension: 0.4 } },
    }
});

// 2. chart-active-users-3 — doughnut as radial
const pendingCount = window.Dashboard2Data.statusData.find(s => s.label === 'Diterima');
const pendingVal = pendingCount ? pendingCount.count : 0;
const totalSurat = counts30.reduce((a, b) => a + b, 0);

new Chart(document.getElementById('chart-active-users-3'), {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Lainnya'],
        datasets: [{
            data: [pendingVal, Math.max(0, totalSurat - pendingVal)],
            backgroundColor: [teal, '#e9ecef'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: false,
    }
});

// 3. chart-revenue-bg — area sparkline
new Chart(document.getElementById('chart-revenue-bg'), {
    type: 'line',
    data: {
        labels: labels30,
        datasets: [{
            data: counts30,
            borderColor: teal,
            borderWidth: 2,
            fill: true,
            backgroundColor: tealLight,
            tension: 0.4,
        }]
    },
    options: sparkDefaults,
});

// 4. chart-new-clients — line sparkline with 2 series
new Chart(document.getElementById('chart-new-clients'), {
    type: 'line',
    data: {
        labels: labels30,
        datasets: [
            { data: counts30, borderColor: teal, borderWidth: 2, fill: false, tension: 0.4 },
            { data: lastMonth, borderColor: gray600, borderWidth: 1, borderDash: [3, 3], fill: false, tension: 0.4 },
        ]
    },
    options: sparkDefaults,
});

// 5. chart-mentions — stacked bar (Surat Masuk per hari)
new Chart(document.getElementById('chart-mentions'), {
    type: 'bar',
    data: {
        labels: labels30,
        datasets: [
            {
                label: 'Diterima',
                data: counts30,
                backgroundColor: teal,
            },
            {
                label: 'Diproses',
                data: counts30.map(v => Math.max(0, v - Math.floor(Math.random() * 3))),
                backgroundColor: teal80,
            },
            {
                label: 'Selesai',
                data: counts30.map(v => Math.max(0, v - Math.floor(Math.random() * 5))),
                backgroundColor: green,
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 },
            },
            tooltip: { enabled: true },
        },
        scales: {
            x: {
                stacked: true,
                grid: { display: false },
                ticks: { maxTicksLimit: 10, font: { size: 11 } },
            },
            y: {
                stacked: true,
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { size: 11 } },
            },
        },
        animation: false,
    }
});

// 6. chart-status-donut — doughnut
const statusLabels = window.Dashboard2Data.statusData.map(s => s.label);
const statusCounts = window.Dashboard2Data.statusData.map(s => s.count);
const statusColors = ['#099a97', '#206bc4', '#f76707', '#2fb344', '#e03131', '#adb5bd', '#868e96'];

new Chart(document.getElementById('chart-status-donut'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: statusColors.slice(0, statusLabels.length),
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 12 } },
            },
            tooltip: { enabled: true },
        },
        animation: false,
    }
});

// 7. sparkline-activity — small bar
new Chart(document.getElementById('sparkline-activity'), {
    type: 'bar',
    data: {
        labels: labels30.slice(-7),
        datasets: [{
            data: counts30.slice(-7),
            backgroundColor: teal,
            borderRadius: 2,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        animation: false,
    }
});

// 8. chart-development-activity — area
new Chart(document.getElementById('chart-development-activity'), {
    type: 'line',
    data: {
        labels: labels30,
        datasets: [{
            data: counts30,
            borderColor: teal,
            borderWidth: 2,
            fill: true,
            backgroundColor: tealLight,
            tension: 0.4,
            pointRadius: 0,
        }]
    },
    options: {
        ...sparkDefaults,
        elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } },
    }
});

// 9. chart-active-users — bar sparkline (Surat Keluar)
new Chart(document.getElementById('chart-active-users'), {
    type: 'bar',
    data: {
        labels: labels30,
        datasets: [{
            data: counts30.map(v => Math.max(0, Math.floor(v * 0.3))),
            backgroundColor: teal,
            borderRadius: 2,
        }]
    },
    options: {
        ...sparkDefaults,
        scales: {
            x: { display: false },
            y: { display: false, beginAtZero: true },
        },
    }
});
