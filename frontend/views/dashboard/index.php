<?php
use yii\helpers\Html;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile('@web/js/dashboard.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">

    <!-- TÍTULO -->
    <div class="mb-4">
        <h4 class="fw-bold text-dark">Dashboard</h4>
        <p class="text-muted mb-0">
            <?= $isTechnician ? 'Visão geral de todos os contadores' : 'Resumo dos seus contadores' ?>
        </p>
    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Ativos</h6>
<!--                    <h3 class="fw-bold text-success">--><?php //= $ativos ?><!--</h3>-->
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Com Problema</h6>
<!--                    <h3 class="fw-bold text-warning">--><?php //= $comProblema ?><!--</h3>-->
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted">Inativos</h6>
<!--                    <h3 class="fw-bold text-danger">--><?php //= $inativos ?><!--</h3>-->
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

            <div class="mt-3">
                <span class="badge bg-success me-2">Ativos</span>
                <span class="badge bg-warning me-2">Com Problema</span>
                <span class="badge bg-danger">Inativos</span>
            </div>
        </div>
    </div>

    <!-- COM / SEM PROBLEMA -->
    <div class="row g-5 mb-4">

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Leituras sem problema</h6>
                        <h3 class="fw-bold text-success mb-0">
<!--                            --><?php //= $readingsSemProblema ?>
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
                        <h6 class="text-muted mb-1">Leituras com problema</h6>
                        <h3 class="fw-bold text-danger mb-0">
<!--                            --><?php //= $readingsComProblema ?>
                        </h3>
                    </div>
                    <i class="fas fa-exclamation-triangle text-danger fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    <?php
/*    $totalReadings = $readingsComProblema + $readingsSemProblema;
    $percentProblema = $totalReadings > 0
            ? round(($readingsComProblema / $totalReadings) * 100)
            : 0;
    */?>

    <div class="alert alert-warning shadow-sm rounded-4">
<!--        <strong>--><?php //= $percentProblema ?><!--%</strong> das leituras apresentam problemas.-->
    </div>

</div>


