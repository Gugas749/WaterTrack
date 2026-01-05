<?php
use yii\helpers\Html;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_END]);
?>

<div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">

    <!-- TÍTULO -->
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Dashboard</h4>
        <p class="text-muted mb-0">
            <?= $isTechnician ? 'Visão geral de todos os contadores da Empresa' : 'Resumo dos seus contadores' ?>
        </p>
    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Ativos</h6>
                    <h3 class="fw-bold text-success"><?= $activeMeters ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Com Problema</h6>
                    <h3 class="fw-bold text-warning"><?= $problemMeters ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Inativos</h6>
                    <h3 class="fw-bold text-danger"><?= $inactiveMeters ?></h3>
                </div>
            </div>
        </div>

    </div>

    <!-- GRÁFICO -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body text-center">
            <h6 class="fw-bold mb-3">Resumo de Contadores</h6>

            <div style="max-width:420px; margin:0 auto;">
                <canvas id="metersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- COM / SEM PROBLEMA -->
    <div class="row g-5 mb-4">

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Leituras</h6>
                        <h3 class="fw-bold text-success mb-0">
                            <?= $readingsCount ?>
                        </h3>
                    </div>
                    <i class="fas fa-check-circle text-success fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Relatórios</h6>
                        <h3 class="fw-bold text-danger mb-0">
                            <?= $reportsCount ?>
                        </h3>
                    </div>
                    <i class="fas fa-exclamation-triangle text-danger fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gráfico donut
        new Chart(document.getElementById("metersChart"), {
            type: 'doughnut',
            data: {
                labels: ['Ativos', 'Com Problema', 'Inativos'],
                datasets: [{
                    data: [<?= $activeMeters ?>, <?= $problemMeters ?>, <?= $inactiveMeters ?>],
                    backgroundColor: ['#4f46e5', '#f59e0b', '#ef4444'],
                    cutout: '70%'
                }]
            },
            options: {plugins: {legend: {position: 'bottom'}}}
        });
    });
</script>

