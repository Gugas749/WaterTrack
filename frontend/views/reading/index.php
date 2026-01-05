<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Meterreading;
use yii\widgets\Pjax;

$this->title = 'Leituras de Contadores';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css');
$this->registerJsFile('@web/js/main-index.js');

$addReading = new Meterreading();
?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">
        <?php Pjax::begin([
                'id' => 'readingsTable',
                'timeout' => 5000,
                'enablePushState' => false, // important
        ]); ?>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Leituras de Contadores</h4>

            <!-- Dropdown selection -->
            <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'action' => ['reading/index'],
                    'options' => [
                            'data' => ['pjax' => true],
                            'class' => 'd-flex align-items-center gap-3'
                    ],
            ]); ?>

            <?= Html::dropDownList(
                    'meter_id',
                    $selectedMeterId ?? null,
                    $meterItems ?? [],
                    [
                            'class' => 'form-select',
                            'prompt' => 'Selecione um Contador',
                            'onchange' => '$("#readingsTable form").submit();',
                    ]
            ) ?>

            <?php ActiveForm::end(); ?>

            <?php if ($isTechnician): ?>
                <button class="btn btn-danger"
                        data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Nova Leitura
                </button>
            <?php endif; ?>
        </div>
        <!-- Table -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">

                <h6 class="fw-bold text-secondary mb-3">
                    Total de Leituras: <?= count($readings) ?>
                </h6>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>Contador</th>
                            <th>Leitura</th>
                            <th>Consumo Acumulado</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php if ($readings): ?>
                            <?php foreach ($readings as $reading): ?>
                                <tr>
                                    <td><?= $reading->meter->address ?></td>
                                    <td><?= Html::encode($reading->reading) ?></td>
                                    <td><?= Html::encode($reading->accumulatedConsumption) ?></td>
                                    <td><?= Html::encode($reading->date) ?></td>
                                    <td>
                                        <?= Html::button('Ver Detalhes', [
                                                'class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm',
                                                'onclick' => "window.location.href='" . Url::to(['reading/index', 'id' => $reading->id]) . "'",
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhuma leitura encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <?php Pjax::end(); ?>
        <!-- Right Panel (ADD) -->
        <?php if ($isTechnician): ?>
            <div id="rightPanel" class="right-panel bg-white shadow" style="display:none;">
                <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h5 class="fw-bold text-dark">Nova Leitura</h5>
                    <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-3">
                    <?php $form = ActiveForm::begin([
                            'id' => 'add-reading-form',
                            'action' => ['reading/create'],
                            'method' => 'post'
                    ]); ?>

                    <h6 class="fw-bold text-dark m-2">Contador</h6>
                    <?= $form->field($addReading, 'meterID')->dropDownList($meterOptions, ['prompt' => 'Selecione o contador'])->label(false) ?>

                    <h6 class="fw-bold text-dark m-2">Leitura</h6>
                    <?= $form->field($addReading, 'reading')->textInput(['placeholder' => 'Indique a leitura'])->label(false) ?>

                    <h6 class="fw-bold text-dark m-2">Consumo Acumulado</h6>
                    <?= $form->field($addReading, 'accumulatedConsumption')->textInput(['placeholder' => 'Indique o consumo acumulado'])->label(false) ?>

                    <h6 class="fw-bold text-dark m-2">Pressão da Água</h6>
                    <?= $form->field($addReading, 'waterPressure')->textInput(['placeholder' => 'Indique a pressão da água'])->label(false) ?>

                    <h6 class="fw-bold text-dark m-2">Data da Leitura</h6>
                    <?= $form->field($addReading, 'date')->input('date')->label(false) ?>

                    <div class="text-center mt-3">
                        <?= Html::submitButton('Criar Leitura', ['class' => 'btn btn-danger ', 'style' => 'background-color:#4f46e5; border:none;']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const openBtn = document.querySelector('[data-toggle="right-panel"]');
                    const panel = document.getElementById('rightPanel');
                    const closeBtn = document.getElementById('closeRightPanel');
                    let overlay = document.getElementById('overlay');

                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'overlay';
                        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:999;display:none;';
                        document.body.appendChild(overlay);
                    }

                    openBtn.addEventListener('click', () => {
                        panel.style.display = 'block';
                        overlay.style.display = 'block';
                        document.body.style.overflow = 'hidden';
                    });

                    closeBtn.addEventListener('click', () => {
                        panel.style.display = 'none';
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    });

                    overlay.addEventListener('click', () => {
                        panel.style.display = 'none';
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    });
                });
            </script>
        <?php endif; ?>

        <!-- Detail Panel -->
        <?php if ($detailReading): ?>
            <div id="detailPanel" class="detail-panel show bg-white shadow">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold">Detalhes da Leitura</h5>
                        <button class="btn btn-sm btn-light" onclick="closePanels()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php if ($isTechnician): ?>
                        <?php $form = ActiveForm::begin([
                                'action' => ['reading/update', 'id' => $detailReading->id],
                                'method' => 'post'
                        ]); ?>
                    <?php endif; ?>

                    <div class="row g-1">
                        <div class="col-md-2">
                            <?= $form->field($detailReading, 'id')->textInput(['disabled' => true])->label('Referência') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'technicianName')
                                    ->textInput([
                                            'value' => $technician->username ?? '',
                                            'disabled' => true
                                    ])
                                    ->label('Criador da leitura') ?>
                        </div>

                        <div class="col-md-5">
                            <?= $form->field($detailReading, 'meterAddress')
                                    ->textInput([
                                            'value' => $detailReading->meter->address ?? '',
                                            'disabled' => true
                                    ])
                                    ->label('Morada do Contador') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'reading')->textInput()->label('Leitura') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'accumulatedConsumption')->textInput()->label('Consumo acumulado') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($detailReading, 'waterPressure')->textInput()->label('Pressão da Água') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($detailReading, 'date')->textInput(['disabled' => true])->label('Data') ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-light" onclick="closePanels()">Fechar</button>
                        <?php if ($isTechnician): ?>
                            <?= Html::submitButton('Editar', ['class' => 'btn btn-danger ', 'style' => 'background-color:#4f46e5; border:none;']) ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($isTechnician) ActiveForm::end(); ?>

                </div>
            </div>

            <script>
                function closePanels() {
                    window.location.href = '<?= Url::to(['reading/index']) ?>';
                }

                document.addEventListener('DOMContentLoaded', () => {
                    const overlay = document.getElementById('overlay');
                    if (overlay) overlay.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            </script>
        <?php endif; ?>

        <div id="overlay"></div>
    </div>
</div>
