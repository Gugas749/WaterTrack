<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use common\models\Meterreading;
use common\models\Meter;
use common\models\User;

// USERS
$users = User::find()->all();
$userOptions = [];
foreach ($users as $u) {
    $userOptions[$u->id] = $u->username;
}

// METERS
$meters = Meter::find()->all();
$meterOptions = [];
foreach ($meters as $m) {
    $meterOptions[$m->id] = 'Contador #' . $m->id . ' - ' . $m->address;
}

$this->title = 'Leituras de Contadores';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

$addReading = new Meterreading();

$readingTypeOptions = [
    0 => 'Manual',
    1 => 'Automática',
];

$problemStateOptions = [
    0 => 'Sem Problema',
    1 => 'Com Problema',
];

?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Leituras de Contadores</h4>

            <?php if (!empty($isTechnician) && $isTechnician): ?>
                <button class="btn btn-danger"
                        data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Nova Leitura
                </button>
            <?php endif; ?>
        </div>

        <!-- TABLE -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">

                <h6 class="fw-bold text-secondary mb-3">
                    Total de Leituras: <?= count($readings) ?>
                </h6>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>ID</th>
                            <th>Contador</th>
                            <th>Leitura</th>
                            <th>Consumo Acumulado</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php if (!empty($readings)): ?>
                            <?php foreach ($readings as $reading): ?>
                                <tr>
                                    <td><?= $reading->id ?></td>
                                    <td><?= 'Contador #' . $reading->meterID ?></td>
                                    <td><?= Html::encode($reading->reading) ?></td>
                                    <td><?= Html::encode($reading->accumulatedConsumption) ?></td>
                                    <td><?= Html::encode($reading->date) ?></td>
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

        <?php if (!empty($isTechnician) && $isTechnician): ?>
            <div id="rightPanel" class="right-panel bg-white shadow" style="display:none;">
                <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h5 class="fw-bold text-dark">Nova Leitura</h5>
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

                    <!-- CONTADOR -->
                    <div class="mb-3">
                        <?= $form->field($addReading, 'meterID')
                                ->dropDownList($meterOptions, ['prompt' => 'Selecione o contador']) ?>
                    </div>

                    <?= $form->field($addReading, 'userID')->hiddenInput()->label(false) ?>
                    <?= $form->field($addReading, 'reading') ?>
                    <?= $form->field($addReading, 'accumulatedConsumption') ?>
                    <?= $form->field($addReading, 'waterPressure') ?>
                    <?= $form->field($addReading, 'readingType')->dropDownList($readingTypeOptions) ?>
                    <?= $form->field($addReading, 'problemState')->dropDownList($problemStateOptions) ?>
                    <?= $form->field($addReading, 'desc')->textarea() ?>
                    <?= $form->field($addReading, 'date')->input('date') ?>

                    <div class="text-end mt-3">
                        <?= Html::submitButton('Criar Leitura', ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?php \yii\widgets\ActiveForm::end(); ?>
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


        <!-- DETAIL PANEL -->
        <?php if ($detailReading): ?>

            <div id="detailPanel" class="detail-panel show bg-white shadow">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold">Detalhes da Leitura</h5>
                        <button class="btn btn-sm btn-light" onclick="closePanels()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php
                    if (!empty($isTechnician) && $isTechnician) {
                        $form = ActiveForm::begin([
                            'action' => ['reading/update', 'id' => $detailReading->id],
                            'method' => 'post'
                        ]);
                    }
                    ?>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Leitura</label>
                            <?= !empty($form)
                                ? $form->field($detailReading, 'reading')->label(false)
                                : Html::input('text', null, $detailReading->reading, ['class'=>'form-control', 'readonly'=>true]) ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Consumo</label>
                            <?= !empty($form)
                                ? $form->field($detailReading, 'accumulatedConsumption')->label(false)
                                : Html::input('text', null, $detailReading->accumulatedConsumption, ['class'=>'form-control', 'readonly'=>true]) ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Pressão</label>
                            <?= !empty($form)
                                ? $form->field($detailReading, 'waterPressure')->label(false)
                                : Html::input('text', null, $detailReading->waterPressure, ['class'=>'form-control', 'readonly'=>true]) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= !empty($form)
                            ? $form->field($detailReading, 'desc')
                            : Html::textarea(null, $detailReading->desc, ['class'=>'form-control', 'readonly'=>true]) ?>
                    </div>

                    <div class="row g-2 mb-3">

                        <!-- READING TYPE -->
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Leitura</label>

                            <?= !empty($form)
                                ? $form->field($detailReading, 'readingType')
                                    ->dropDownList($readingTypeOptions)
                                    ->label(false)
                                : Html::input(
                                    'text',
                                    null,
                                    $readingTypeOptions[$detailReading->readingType] ?? '—',
                                    ['class' => 'form-control', 'readonly' => true]
                                )
                            ?>
                        </div>

                        <!-- PROBLEM STATE -->
                        <div class="col-md-4">
                            <label class="form-label">Estado do Problema</label>

                            <?= !empty($form)
                                ? $form->field($detailReading, 'problemState')
                                    ->dropDownList($problemStateOptions)
                                    ->label(false)
                                : Html::input(
                                    'text',
                                    null,
                                    $problemStateOptions[$detailReading->problemState] ?? '—',
                                    ['class' => 'form-control', 'readonly' => true]
                                )
                            ?>
                        </div>

                        <!-- DATE -->
                        <div class="col-md-4">
                            <label class="form-label">Data</label>

                            <?= !empty($form)
                                ? $form->field($detailReading, 'date')
                                    ->input('date')
                                    ->label(false)
                                : Html::input(
                                    'text',
                                    null,
                                    $detailReading->date,
                                    ['class' => 'form-control', 'readonly' => true]
                                )
                            ?>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-light" onclick="closePanels()">Fechar</button>
                        <?php if (!empty($form)): ?>
                            <?= Html::submitButton('Salvar', ['class'=>'btn btn-primary']) ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($form)) ActiveForm::end(); ?>

                </div>
            </div>
            <script>
                function closePanels() {
                    window.location.href = '<?= Url::to(['reading/index']) ?>';
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


    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const meterSelect = document.getElementById('meterreading-meterid');
            const userInput   = document.getElementById('meterreading-userid');

            if (!meterSelect || !userInput) return;

            meterSelect.addEventListener('change', function () {
                const meterID = this.value;

                if (!meterID) {
                    userInput.value = '';
                    return;
                }

                fetch('<?= Url::to(['reading/get-user-by-meter']) ?>?id=' + meterID)
                    .then(response => response.json())
                    .then(data => {
                        userInput.value = data.userID ?? '';
                    });
            });
        });
    </script>
</div>


