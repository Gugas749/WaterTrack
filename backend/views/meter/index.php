<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

// PAGE SETTINGS
$this->title = 'Contadores';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('@web/js/meter-index-form.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

// OPTIONS
$classOptions = [
        'A' => 'Classe A',
        'B' => 'Classe B',
        'C' => 'Classe C',
        'D' => 'Classe D',
];
$measureUnityOptions = [
        '1' => 'm^3',
        '2' => 'm^3/h',
        '3' => 'L/s',
        '4' => 'bar',
        '5' => 'Litros',
        '6' => 'Decilitros',
];
$stateOptions = [
        '1' => 'ATIVO',
        '2' => 'COM PROBLEMA',
        '0' => 'INATIVO',
];
$statusClasses = [
        1 => 'text-success',
        2 => 'text-warning',
        0 => 'text-danger',
];
$stateClasses = [
        1 => 'bg-success',
        2 => 'bg-warning',
        0 => 'bg-danger',
];
$statusClass = match ($meter->state ?? null) {
    1 => 'bg-success',
    2  => 'bg-warning',
    0  => 'bg-danger',
    default => 'bg-secundary',
};
$statusText = match ($meter->state ?? null) {
    1 => 'ATIVO',
    2  => 'COM PROBLEMA',
    0  => 'DESATIVADO',
    default => 'DESCONHECIDO',
};

?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">
        <?php Pjax::begin([
                'id' => 'metersTable',
                'timeout' => 5000,
                'enablePushState' => false, // important
        ]); ?>
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Contadores</h4>
            <div class="d-flex align-items-center gap-3">
                <!-- SEARCH -->
                <div class="input-group mx-5" style="width:220px;">
                    <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['meter/index'],
                            'options' => ['data' => ['pjax' => true], 'class' => 'd-flex align-items-center w-100'],
                    ]); ?>
                    <input type="text" name="q"
                           class="form-control form-control-sm ps-3 pe-5"
                           placeholder="Search"
                           value="<?= Html::encode($search) ?>"
                           style="border:1px solid #e5e7eb;">
                    <button type="submit" class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>

                <!-- ADD BUTTON -->
                <button class="btn btn-primary" data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Adicionar Contador
                </button>
            </div>
        </div>
        <!-- ALERT MESSAGES -->
        <div id="flash-container">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <?php
                $bgClass = match($type) {
                    'error' => 'bg-danger text-white',
                    'success' => 'bg-success text-white',
                    default => 'bg-info text-white',
                };
                ?>
                <div class="toast show <?= $bgClass ?> ms-auto" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-bell-fill me-2"></i><?= $message ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- TABLE -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">
                <h6 class="fw-bold text-secondary mb-3">
                    Total de Contadores: <?= count($meters) ?>
                </h6>
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
                                    <td><?= htmlspecialchars($meter->id) ?></td>

                                    <td>
                                        <a href="https://www.google.com/maps/search/<?= urlencode($meter->address) ?>"
                                           class="text-decoration-none text-primary">
                                            <?= htmlspecialchars($meter->address) ?>
                                        </a>
                                    </td>

                                    <td><?= htmlspecialchars($meter->instalationDate ?? 'N/A') ?></td>

                                    <td>
                                        <?php $form = \yii\widgets\ActiveForm::begin([
                                                'action' => ['update-state', 'id' => $meter->id],
                                                'method' => 'post'
                                        ]); ?>

                                        <?= Html::dropDownList(
                                                'state',
                                                $meter->state,
                                                $stateOptions,
                                                [
                                                        'class' => 'form-select form-select-sm fw-bold ' . ($statusClasses[$meter->state] ?? 'text-muted'),
                                                        'onchange' => 'this.form.submit()',
                                                        'options' => [
                                                                1 => ['class' => 'text-success'],
                                                                2 => ['class' => 'text-warning'],
                                                                0 => ['class' => 'text-danger'],
                                                        ]
                                                ]
                                        ) ?>

                                        <?php \yii\widgets\ActiveForm::end(); ?>
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
        <?php Pjax::end(); ?>
        <!-- RIGHT PANEL -->
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

                <?= $form->field($addMeterModel, 'enterpriseID')->dropDownList(
                        ArrayHelper::map($enterprises, 'id', fn($e) => $e->id . ' - ' . $e->name),
                        ['prompt' => 'Selecione a Empresa']
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
        <!-- DETAIL PANEL -->
        <?php if ($detailMeter): ?>
            <div id="detailPanel" class="detail-panel bg-white shadow show">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <!-- TÍTULO + BOTÃO FECHAR -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Contador</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'update-meter-form',
                            'action' => ['update', 'id' => $detailMeter->id],
                            'method' => 'post'
                    ]); ?>

                    <div class="mb-4">
                        <?php
                        $statusClass = $stateClasses[$detailMeter->state ?? 0] ?? 'bg-secondary';
                        $statusText = $stateOptions[$detailMeter->state ?? 0] ?? 'DESCONHECIDO';
                        ?>
                        <span id="meter-status-badge" class="badge <?= $statusClass ?> px-3 py-2"><?= $statusText ?></span>
                    </div>

                    <!--IDENTIFICAÇÃO-->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Identificação</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'id')->textInput(['readonly' => true])->label('Referência') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'meterTypeID')->dropDownList(
                                    ArrayHelper::map($meterTypes, 'id', fn($t) => $t->description)
                            )->label('Tipo de Contador') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'class')->dropDownList($classOptions)->label('Classe') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'state')->dropDownList(
                                    $stateOptions,
                                    [
                                            'id' => 'meter-status-dropdown',
                                            'class' => 'form-select fw-bold ' . ($statusClasses[$detailMeter->state] ?? 'text-muted'),
                                            'options' => [
                                                    1 => ['class' => 'text-success'],
                                                    2 => ['class' => 'text-warning'],
                                                    0 => ['class' => 'text-danger'],
                                            ]
                                    ])->label('Estado') ?>
                        </div>
                    </div>

                    <!--LOCALIZAÇÃO-->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Localização e Associação</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <?= $form->field($detailMeter, 'address')->textInput()->label('Morada') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'userID')->dropDownList(
                                    ArrayHelper::map($users, 'id', fn($u) => $u->id . ' - ' . $u->username)
                            )->label('Utilizador') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($detailMeter, 'enterpriseID')->dropDownList(
                                    ArrayHelper::map($enterprises, 'id', fn($e) => $e->name)
                            )->label('Empresa') ?>
                        </div>
                    </div>

                    <!--SPECTS-->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Especificações Técnicas</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <?= $form->field($detailMeter, 'maxCapacity')->textInput()->label('Capacidade Máxima') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($detailMeter, 'measureUnity')->dropDownList($measureUnityOptions)
                                    ->label('Unidade de Medida') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($detailMeter, 'supportedTemperature')->textInput()
                                    ->label('Temperatura Suportada') ?>
                        </div>
                    </div>

                    <!--DATAS-->
                    <h6 class="fw-bold text-secondary mt-3 mb-2">Datas</h6>

                    <div class="row g-2 mb-4">
                        <div class="col-md-4">
                            <?= $form->field($detailMeter, 'instalationDate')->textInput(['readonly' => true])
                                    ->label('Data de Instalação') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($detailMeter, 'shutdownDate')->textInput(['readonly' => true])
                                    ->label('Data de Desativação') ?>
                        </div>
                    </div>

                    <!-- BOTÕES -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="closeDetailPanel btn btn-light px-4 py-2">Fechar</button>
                        <?= Html::submitButton('Salvar', ['class' => 'btn btn-primary px-4 py-2']) ?>
                    </div>

                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    document.getElementById('overlay').style.display = 'block';
                    document.getElementById('detailPanel').style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            </script>
        <?php endif; ?>


        <!-- OVERLAY -->
        <div id="overlay"></div>
    </div>
</div>

<script>
    $(document).on('pjax:end', function() {
        // Update state dropdown colors after PJAX reload
        $('.meter-state-dropdown').each(function() {
            const val = $(this).val();
            const classes = { 1:'text-success', 2:'text-warning', 0:'text-danger' };
            $(this).removeClass('text-success text-warning text-danger')
                .addClass(classes[val] || 'text-muted');
        });

        // Show flash messages
        $('#flash-container .toast').each(function() {
            const toastEl = this;
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
        });
    });

    $(document).on('change', 'select[name="state"]', function() {
        const val = $(this).val();
        const classes = {
            1: 'text-success',
            2: 'text-warning',
            0: 'text-danger'
        };
        $(this).removeClass('text-success text-warning text-danger')
            .addClass(classes[val] || 'text-muted');
    });
</script>