import * as echarts from 'echarts';

const chartEl = document.getElementById('chart-tren');
if (chartEl) {
    const url = chartEl.dataset.url || '/api/tracking/chart';
    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        const chart = echarts.init(chartEl);
        chart.setOption({
            tooltip: { trigger: 'axis' },
            xAxis: { type: 'category', data: data.labels },
            yAxis: { type: 'value' },
            series: [{
                type: 'line',
                data: data.values,
                smooth: true,
                areaStyle: { opacity: 0.1 }
            }]
        });
        window.addEventListener('resize', () => chart.resize());
    });
}
