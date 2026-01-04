<?php

use yii\bootstrap5\Html;

$this->title = 'Dashboard';
$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>
<?php
$currentYear = Yii::$app->request->get('year', date('Y')); // default current year
$prevYear = $currentYear - 1;
$nextYear = $currentYear + 1;
?>
<div class="content">

    <!-- Info Boxes -->
    <div class="container d-flex justify-content-center flex-wrap mt-5">
        <div class="col-lg-3 col-md-4 col-sm-6 col-10 m-2">
            <?= \hail812\adminlte\widgets\InfoBox::widget([
                'text' => 'Total de Contadores Ativos',
                'number' => $activeMeterCount,
                'icon' => 'fas fa-tint',
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-10 m-2">
            <?= \hail812\adminlte\widgets\InfoBox::widget([
                'text' => 'Total de Leituas Criadas',
                'number' => $readingCount,
                'icon' => 'fas fa-clipboard-list',
            ]) ?>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-10 m-2">
            <?= \hail812\adminlte\widgets\InfoBox::widget([
                'text' => 'Total de Utilizadores',
                'number' => $userCount,
                'icon' => 'fas fa-user',
            ]) ?>
        </div>
    </div>

    <!-- Corpo da Dashboard -->
    <div class="container-fluid py-4" style="background-color:#f8f9fc;">
        <div class="row justify-content-center">

            <!-- Gráfico de Leituras -->
            <div class="col-lg-7 col-md-12 mb-4">
                <div class="card shadow-sm border-0" style="border-radius:16px;">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-secondary">Gráfico de Leituras por Mês</h6>
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <a href="<?= \yii\helpers\Url::current(['year' => $prevYear]) ?>" class="btn btn-outline-primary me-3">&larr;</a>
                            <span class="h5 mb-0"><?= $currentYear ?></span>
                            <a href="<?= \yii\helpers\Url::current(['year' => $nextYear]) ?>" class="btn btn-outline-primary ms-3">&rarr;</a>
                        </div>
                        <canvas id="chartLeituras" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico Donut -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0" style="border-radius:16px;">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-secondary mb-3">Resumo de Contadores</h6>
                        <canvas id="chartDonut" height="160"></canvas>
                    </div>
                </div>
            </div>

            <!-- Histórico de Leituras -->
            <div class="col-lg-11 col-md-12">
                <div class="card shadow-sm border-0" style="border-radius:16px;">
                    <div class="card-body">
                        <h6 class="fw-bold text-secondary mb-3">Histórico de Leituras Recentes</h6>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="text-muted small">
                                <tr>
                                    <th>Referência Leitura</th>
                                    <th>Consumo Acumulado</th>
                                    <th>Leitura</th>
                                    <th>Data da Leitura</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lastReadings)): ?>
                                    <?php foreach ($lastReadings as $reading): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($reading->id) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($reading->accumulatedConsumption ?? 'N/A') ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($reading->reading ?? 'N/A') ?>
                                            </td>

                                            <td><?= htmlspecialchars($reading->date ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Nenhuma leitura encontrada.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Gráfico de barras
        new Chart(document.getElementById("chartLeituras"), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [{
                    label: 'Leituras',
                    data: [<?= count($readingsJan) ?>,
                        <?= count($readingsFev) ?>,
                        <?= count($readingsMar) ?>,
                        <?= count($readingsAbr) ?>,
                        <?= count($readingsMai) ?>,
                        <?= count($readingsJun) ?>,
                        <?= count($readingsJul) ?>,
                        <?= count($readingsAgo) ?>,
                        <?= count($readingsSet) ?>,
                        <?= count($readingsOut) ?>,
                        <?= count($readingsNov) ?>,
                        <?= count($readingsDez) ?>],
                    backgroundColor: '#4f46e5',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {legend: {display: false}},
                scales: {
                    y: {beginAtZero: true, ticks: {color: '#6b7280'}},
                    x: {ticks: {color: '#6b7280'}}
                }
            }
        });

        // Gráfico donut
        new Chart(document.getElementById("chartDonut"), {
            type: 'doughnut',
            data: {
                labels: ['Ativos', 'Com Problema', 'Inativos'],
                datasets: [{
                    data: [<?= count($metersActive) ?>, <?= count($metersProblem) ?>, <?= count($metersInactive) ?>],
                    backgroundColor: ['#4f46e5', '#f59e0b', '#ef4444'],
                    cutout: '70%'
                }]
            },
            options: {plugins: {legend: {position: 'bottom'}}}
        });
    });
</script>

<style>
    body {
        overflow-x: hidden;
    }

    .container, .container-fluid {
        max-width: 100vw;
        overflow-x: hidden;
    }

    .card {
        transition: all .3s ease;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
    }
</style>
