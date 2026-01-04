document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('metersChart');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Ativos', 'Com Problema', 'Inativos'],
            datasets: [{
                data: window.dashboardData,
                backgroundColor: ['#4ade80', '#facc15', '#f87171']
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});