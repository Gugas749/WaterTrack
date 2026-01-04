<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\models\Enterprise;
use common\models\Userprofile;
use yii\web\JsExpression;
use yii\widgets\Pjax;

$this->title = 'Leituras';
$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/reading-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
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
            <h4 class="fw-bold text-dark">Leituras</h4>
            <div class="d-flex align-items-center gap-3">
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
                        'enterprise_id',
                        $selectedEnterpriseId ?? null,
                        $enterpriseItems,
                        [
                                'class' => 'form-select',
                                'prompt' => 'Selecione uma Empresa',
                                'onchange' => '$("#readingsTable form").submit();',
                        ]
                ) ?>

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

                <!-- Open Panel Button -->
                <button class="btn btn-primary"
                        data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Adicionar Leitura
                </button>
            </div>
        </div>
        <!-- Cards superiores -->
        <div class="row g-4 px-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4" style="background:white;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Média do Consumo Acumulado</h6>
                                <h3 class="fw-bold mb-0 text-dark"><?= $accumulatedConsumption ?></h3>
<!--                                <small class="text-success fw-semibold">+1,400 Novos</small>-->
                            </div>
                            <div class="text-primary fs-3"><i class="fas fa-tint"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 rounded-4" style="background:white;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted mb-1">Média da Pressão</h6>
                                <h3 class="fw-bold mb-0 text-dark"><?= $waterPressure ?></h3>
<!--                                <small class="text-success fw-semibold">+1,000 Hoje</small>-->
                            </div>
                            <div class="text-warning fs-3"><i class="fas fa-gauge-high"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Corpo principal -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <h6 class="fw-bold text-secondary mb-3">Histórico de Leituras</h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>Referência Leitura</th>
                            <th>Consumo Acumulado</th>
                            <th>Leitura</th>
                            <th>Data da Leitura</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($readings)): ?>
                                <?php foreach ($readings as $reading): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($reading->id) ?>
                                            <?php if($reading->readingType == 1): ?>
                                                <i class="fas fa-wrench ms-2"></i>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($reading->accumulatedConsumption ?? 'N/A') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($reading->reading ?? 'N/A') ?>
                                        </td>

                                        <td><?= htmlspecialchars($reading->date ?? 'N/A') ?></td>

                                        <td>
                                            <?= Html::button('Ver Detalhes', [
                                                    'class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm',
                                                    'onclick' => "window.location.href='" . Url::to(['reading/index', 'id' => $reading->id]) . "'",
                                                    'style' => 'transition:0.2s;',
                                                    'onmouseover' => "this.style.transform='scale(1.05)'",
                                                    'onmouseout'  => "this.style.transform='scale(1)'",
                                            ]) ?>
                                        </td>
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
        <?php Pjax::end(); ?>

        <!-- RIGHT PANEL-->
        <div id="rightPanel" class="right-panel bg-white shadow" style="display:none;">
            <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="fw-bold text-dark">Adicionar Leitura</h5>
                <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-3">
                <?php $form = \yii\widgets\ActiveForm::begin([
                        'id' => 'add-reading-form',
                        'action' => ['reading/create'],
                        'method' => 'post'
                ]); ?>

                <?= $form->field($addReadingModel, 'meterID')->dropDownList(
                        ArrayHelper::map($meters, 'id', fn($m) => $m->id . ' - ' . $m->address),
                        ['prompt' => 'Selecione o Contador']
                ) ?>

                <?= $form->field($addReadingModel, 'userID')->dropDownList(
                        ArrayHelper::map($users, 'id', fn($u) => $u->id . ' - ' . $u->username),
                        ['prompt' => 'Selecione o Utilizador']
                ) ?>

                <?= $form->field($addReadingModel, 'reading')->textInput(['placeholder' => 'Valor da Leitura']) ?>

                <?= $form->field($addReadingModel, 'accumulatedConsumption')->textInput(['placeholder' => 'Consumo Acumulado']) ?>

                <?= $form->field($addReadingModel, 'waterPressure')->textInput(['placeholder' => 'Pressão da Água']) ?>

                <?= $form->field($addReadingModel, 'desc')->textInput(['placeholder' => 'Descrição']) ?>

                <?= $form->field($addReadingModel, 'date')->input('date') ?>

                <?= $form->field($addReadingModel, 'readingType')->dropDownList([
                        0 => 'Sem Problemas',
                        1 => 'Com Problemas',
                ], [
                        'onchange' => new JsExpression("
            if (this.value == 1) {
                document.getElementById('problemContainer').style.display = 'block';
            } else {
                document.getElementById('problemContainer').style.display = 'none';
            }
        "),
                ]) ?>

                <!-- Problema apenas visível se readingType = 1 -->
                <div id="problemContainer" style="display:<?= $addReadingModel->readingType == 1 ? 'block' : 'none' ?>;">
                    <?= $form->field($addReadingModel, 'problemID')->dropDownList(
                            ArrayHelper::map($problems, 'id', 'desc'),
                            ['prompt' => 'Selecione o Problema']
                    ) ?>
                </div>



                <div class="text-end mt-3">
                    <?= Html::submitButton('Criar Leitura', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $('#closeRightPanel').click(function() {
                    $('#rightPanel').hide();
                    $('#overlay').hide();
                    $('body').css('overflow', 'auto');
                });

                // Mostrar campo Problema apenas se readingType == 1
                $('#addreadingmodel-readingtype').change(function() {
                    if ($(this).val() == 1) {
                        $('#problemContainer').show();
                    } else {
                        $('#problemContainer').hide();
                    }
                });
            });
        </script>

        <!-- DETAIL PANEL -->
        <?php if ($detailReading): ?>
            <div id="detailPanel" class="detail-panel bg-white shadow" style="display:none;">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <!-- TÍTULO + BOTÃO FECHAR -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes da Leitura</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'update-reading-form',
                            'action' => ['update', 'id' => $detailReading->id],
                            'method' => 'post'
                    ]); ?>

                    <div class="row g-1">
                        <div class="col-md-2">
                            <?= $form->field($detailReading, 'id')->textInput(['readonly' => true, 'id' => 'detailReadingId'])->label('Referência') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'userID')->textInput([
                                    'value' => htmlspecialchars($technician->username ?? '')
                            ])->label('Username') ?>
                        </div>
                        <div class="col-md-5">
                            <?= $form->field($detailReading, 'meterID')->textInput([
                                    'value' => htmlspecialchars($detailReading->meter->address ?? '')
                            ])->label('Morada do Contador') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'reading')->textInput(['id' => 'detailReadingValue'])->label('Leitura') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailReading, 'accumulatedConsumption')->textInput(['id' => 'detailAccumulatedConsumption'])->label('Consumo acumulado') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($detailReading, 'waterPressure')->textInput(['id' => 'detailWaterPressure'])->label('Pressão da Água') ?>
                        </div>
                        <div class="col-md-11">
                            <?= $form->field($detailReading, 'desc')->textInput(['id' => 'detailDesc'])->label('Descrição') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($detailReading, 'date')->textInput(['readonly' => true, 'id' => 'detailDate'])->label('Data') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailReading, 'readingType')->dropDownList([
                                    0 => 'Sem Problemas',
                                    1 => 'Com Problemas',
                            ], [
                                    'onchange' => new \yii\web\JsExpression("
            var container = document.getElementById('problemContainerDetails');
            var input = document.getElementById('detailProblem');
            if (this.value == 1) {
                container.style.display = 'block';
                input.disabled = false;
            } else {
                container.style.display = 'none';
                input.disabled = true;
                input.value = '';
            }
        "),
                            ])->label('Tipo de Leitura') ?>
                        </div>

                        <div class="col-md-5" id="problemContainerDetails" style="display:<?= ($detailReading->readingType ?? 0) == 1 ? 'block' : 'none' ?>;">
                            <?= $form->field($selectedDetailsProblem, 'desc')->textInput([
                                    'readonly' => true,
                                    'id' => 'detailProblem'
                            ])->label('Problema') ?>
                        </div>
                    </div>

                    <!-- FOOTER BUTTONS -->
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <button type="button" class="closeDetailPanel btn btn-light px-4">Fechar</button>
                        <?= Html::submitButton('Editar', ['class' => 'btn btn-primary px-4 py-2', 'style' => 'background-color:#4f46e5; border:none;', 'id' => 'detailEditButton']) ?>
                    </div>

                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const detailPanel = document.getElementById('detailPanel');
                    const overlay = document.getElementById('overlay');

                    overlay.style.display = 'block';
                    detailPanel.style.display = 'block';
                    document.body.style.overflow = 'hidden';

                    requestAnimationFrame(() => {
                        detailPanel.classList.add('show');
                    });
                });
            </script>
        <?php endif; ?>
        <!-- OVERLAY -->
        <div id="overlay"></div>
    </div>
</div>

