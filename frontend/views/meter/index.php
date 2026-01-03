<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->title = 'Meus Contadores';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('@web/js/meter-index-form.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

$stateOptions = [
        1 => 'ATIVO',
        2 => 'COM PROBLEMA',
        0 => 'INATIVO',
];
$stateClasses = [
        1 => 'text-success',
        2 => 'text-warning',
        0 => 'text-danger',
];
$classOptions = [
        'A' => 'Classe A',
        'B' => 'Classe B',
        'C' => 'Classe C',
        'D' => 'Classe D',
];
$measureUnityOptions = [
        'm3'   => 'm³',
        'm3h'  => 'm³/h',
        'ls'   => 'L/s',
        'bar'  => 'bar',
];

?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Meus Contadores</h4>

            <div class="d-flex align-items-center gap-3">
                <!-- SEARCH -->
                <div class="input-group" style="width:220px;">
                    <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['meter/index'],
                            'options' => ['class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                           class="form-control form-control-sm ps-3 pe-5"
                           placeholder="Pesquisar morada"
                           value="<?= Html::encode(Yii::$app->request->get('q')) ?>"
                           style="border:1px solid #e5e7eb;">
                    <button type="submit" class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>

                <!-- ADD BUTTON (apenas técnico) -->
                <?php if (!empty($isTechnician) && $isTechnician): ?>
                    <button class="btn btn-primary" data-toggle="right-panel"
                            style="background-color:#4f46e5; border:none;">
                        <i class="fas fa-plus me-1"></i> Adicionar Contador
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- TABLE -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">

                <h6 class="fw-bold text-secondary mb-3">Total de Contadores: <?= count($meters) ?></h6>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>Referência</th>
                            <th>Morada</th>
                            <th>Data de Instalação</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($meters)): ?>
                            <?php foreach ($meters as $meter): ?>
                                <tr>
                                    <td><?= Html::encode($meter->id) ?></td>

                                    <td>
                                        <a href="https://www.google.com/maps/search/<?= urlencode($meter->address) ?>"
                                           target="_blank"
                                           class="text-decoration-none text-primary">
                                            <?= Html::encode($meter->address) ?>
                                        </a>
                                    </td>

                                    <td><?= Html::encode($meter->instalationDate ?? 'N/A') ?></td>

                                    <td>
                                        <?php $state = $meter->state ?? 0; ?>
                                        <span class="fw-bold <?= $stateClasses[$state] ?? 'text-muted' ?>">
                                            <?= $stateOptions[$state] ?? 'DESCONHECIDO' ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= Html::button('Ver Detalhes', [
                                                'class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm',
                                                'onclick' => "window.location.href='" . Url::to(['meter/index', 'id' => $meter->id]) . "'",
                                                'style' => 'transition:0.2s;',
                                                'onmouseover' => "this.style.transform='scale(1.05)'",
                                                'onmouseout'  => "this.style.transform='scale(1)'",
                                        ]) ?>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum contador encontrado.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- ADD PANEL (só visível para técnico) -->
        <?php if (!empty($isTechnician) && $isTechnician): ?>

            <div id="rightPanel" class="right-panel bg-white shadow" style="display:none;">
                <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h5 class="fw-bold text-dark">Adicionar Contador</h5>
                    <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-3">
                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'add-meter-form',
                            'action' => ['meter/create'],
                            'method' => 'post'
                    ]); ?>

                    <?= $form->field($addMeterModel, 'address')->textInput(['placeholder' => 'Morada']) ?>

                    <?= $form->field($addMeterModel, 'userID')->dropDownList(
                            ArrayHelper::map($users, 'id', fn($u) => $u->id . ' - ' . $u->username),
                            ['prompt' => 'Selecione o Utilizador']
                    ) ?>

                    <?= $form->field($addMeterModel, 'meterTypeID')->dropDownList(
                            ArrayHelper::map($meterTypes, 'id', fn($t) => $t->id . ' - ' . $t->description),
                            ['prompt' => 'Selecione o Tipo de Contador']
                    ) ?>

                    <?= $form->field($addMeterModel, 'class')->dropDownList($classOptions, ['prompt' => 'Selecione a Classe']) ?>

                    <?= $form->field($addMeterModel, 'instalationDate')->input('date') ?>

                    <?= $form->field($addMeterModel, 'maxCapacity')->textInput(['placeholder' => 'Capacidade Máxima']) ?>

                    <?= $form->field($addMeterModel, 'measureUnity')->dropDownList($measureUnityOptions, ['prompt' => 'Selecione a Unidade de Medida']) ?>

                    <?= $form->field($addMeterModel, 'supportedTemperature')->textInput(['placeholder' => 'Temperatura Suportada']) ?>

                    <?= $form->field($addMeterModel, 'state')->dropDownList($stateOptions, ['prompt' => 'Selecione o Estado']) ?>

                    <div class="text-end mt-3">
                        <?= Html::submitButton('Criar Contador', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>

        <?php endif; ?>

        <!-- DETAIL PANEL -->
        <?php if ($detailMeter): ?>

            <?php
            $badgeClasses = [
                    1 => 'bg-success',
                    2 => 'bg-warning',
                    0 => 'bg-danger'
            ];

            $badgeClass = $badgeClasses[$detailMeter->state ?? 0] ?? 'bg-secondary';
            $badgeText  = $stateOptions[$detailMeter->state ?? 0] ?? 'DESCONHECIDO';
            ?>

            <div id="detailPanel" class="detail-panel bg-white shadow show">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <!-- TÍTULO + BOTÃO FECHAR -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Contador</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="mb-4">
                        <span class="badge <?= $badgeClass ?> px-3 py-2"><?= $badgeText ?></span>
                    </div>

                    <?php
                    // Se for técnico, usamos um ActiveForm que envia para meter/update
                    if (!empty($isTechnician) && $isTechnician) {
                        $detailForm = ActiveForm::begin([
                                'id' => 'update-meter-form',
                                'action' => ['meter/update', 'id' => $detailMeter->id],
                                'method' => 'post',
                        ]);
                    } else {
                        // apenas para exibir (sem form)
                        $detailForm = null;
                    }
                    ?>

                    <!-- IDENTIFICAÇÃO -->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Identificação</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Referência</label>
                            <input class="form-control" readonly value="<?= Html::encode($detailMeter->id) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Classe</label>
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'class')->dropDownList($classOptions)->label(false) ?>
                            <?php else: ?>
                                <input class="form-control" readonly value="<?= Html::encode($detailMeter->class) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'state')->dropDownList(
                                        $stateOptions,
                                        [
                                                'id' => 'meter-status-dropdown',
                                            // aplica a classe no select e garante cor do texto conforme estado atual
                                                'class' => 'form-select fw-bold ' . ($stateClasses[$detailMeter->state] ?? 'text-muted'),
                                                'options' => [
                                                        1 => ['class' => 'text-success'],
                                                        2 => ['class' => 'text-warning'],
                                                        0 => ['class' => 'text-danger'],
                                                ],
                                        ]
                                )->label('Estado') ?>
                            <?php else: ?>
                                <label class="form-label">Estado</label>
                                <input class="form-control fw-bold <?= $detailMeter->state == 1 ? 'text-success' : ($detailMeter->state == 2 ? 'text-warning' : 'text-danger') ?>"
                                       readonly
                                       value="<?= Html::encode($badgeText) ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- LOCALIZAÇÃO -->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Localização</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Morada</label>
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'address')->textInput()->label(false) ?>
                            <?php else: ?>
                                <input class="form-control" readonly value="<?= Html::encode($detailMeter->address) ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ESPECIFICAÇÕES -->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Especificações Técnicas</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Capacidade Máxima</label>
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'maxCapacity')->textInput()->label(false) ?>
                            <?php else: ?>
                                <input class="form-control" readonly value="<?= Html::encode($detailMeter->maxCapacity) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unidade de Medida</label>
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'measureUnity')->dropDownList($measureUnityOptions)->label(false) ?>
                            <?php else: ?>
                                <input class="form-control" readonly value="<?= Html::encode($detailMeter->measureUnity) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Temperatura Suportada</label>
                            <?php if (!empty($isTechnician) && $isTechnician): ?>
                                <?= $detailForm->field($detailMeter, 'supportedTemperature')->textInput()->label(false) ?>
                            <?php else: ?>
                                <input class="form-control" readonly value="<?= Html::encode($detailMeter->supportedTemperature) ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- DATAS -->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Datas</h6>

                    <div class="row g-2 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Data de Instalação</label>
                            <input class="form-control" readonly value="<?= Html::encode($detailMeter->instalationDate) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Desativação</label>
                            <input class="form-control" readonly value="<?= Html::encode($detailMeter->shutdownDate) ?>">
                        </div>
                    </div>

                    <!-- BOTÕES -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light px-4" onclick="closePanels()">Fechar</button>

                        <?php if (!empty($isTechnician) && $isTechnician): ?>
                            <?= Html::submitButton('Salvar', ['class' => 'btn btn-primary px-4']) ?>
                        <?php endif; ?>
                    </div>

                    <?php
                    if (!empty($isTechnician) && $isTechnician) {
                        ActiveForm::end();
                    }
                    ?>

                </div>
            </div>
            <script>
                function closePanels() {
                    window.location.href = '<?= Url::to(['meter/index']) ?>';
                }

                document.addEventListener('DOMContentLoaded', () => {
                    // mostra overlay e painel só se existirem
                    var overlay = document.getElementById('overlay');
                    var detail = document.getElementById('detailPanel');
                    if (overlay) overlay.style.display = 'block';
                    if (detail) detail.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            </script>

        <?php endif; ?>

        <div id="overlay"></div>

    </div>
</div>
