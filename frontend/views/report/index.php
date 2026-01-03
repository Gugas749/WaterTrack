<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use common\models\Meter;
use common\models\Meterproblem;

/* METERS */
$meters = Meter::find()
    ->joinWith('meterreadings') // relação no Meter.php
    ->where(['meterreading.problemState' => 1])
    ->distinct()
    ->all();
$meterOptions = [];
foreach ($meters as $m) {
    $meterOptions[$m->id] = 'Contador #' . $m->id . ' - ' . $m->address;
}

$this->title = 'Relatórios de Problemas';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

$addReport = new Meterproblem();

$problemTypeOptions = [
    'Fuga de água',
    'Contador avariado',
    'Leitura incorreta',
    'Pressão anormal',
    'Danos físicos no contador',
    'Outro',
];
?>

<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Relatórios de Problemas</h4>

            <?php if ($isTechnician): ?>
                <button class="btn btn-danger"
                        onclick="document.getElementById('addPanel').style.display='block';
                                 document.getElementById('overlay').style.display='block'">
                    <i class="fas fa-plus me-1"></i> Novo Relatório
                </button>
            <?php endif; ?>
        </div>

        <!-- TABLE -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">

                <h6 class="fw-bold text-secondary mb-3">
                    Total de Relatórios: <?= count($reports) ?>
                </h6>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>ID</th>
                            <th>Contador</th>
                            <th>Tipo de Problema</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php if (!empty($reports)): ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?= $report->id ?></td>
                                    <td>Contador #<?= $report->meterID ?></td>
                                    <td><?= Html::encode($report->problemType) ?></td>
                                    <td>
                                        <?= Html::button('Ver Detalhes', [
                                            'class' => 'btn btn-outline-danger btn-sm fw-semibold shadow-sm',
                                            'onclick' => "window.location.href='" .
                                                Url::to(['report/index', 'id' => $report->id]) . "'",
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Nenhum relatório encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- ADD PANEL -->
        <?php if ($isTechnician): ?>
            <div id="addPanel" class="detail-panel show bg-white shadow" style="display:none;">
                <div class="modal-content p-4 rounded-4">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold">Novo Relatório</h5>
                        <button class="btn btn-sm btn-light" onclick="closePanels()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['report/create'],
                        'method' => 'post'
                    ]); ?>

                    <?= $form->field($addReport, 'meterID')
                        ->dropDownList($meterOptions, ['prompt' => 'Selecione o contador']) ?>

                    <?= $form->field($addReport, 'problemType')
                        ->dropDownList(
                            array_combine($problemTypeOptions, $problemTypeOptions),
                            ['prompt' => 'Selecione o tipo de problema', 'id' => 'problem-type-select']
                        ) ?>

                    <div id="other-problem-wrapper" class="mb-3" style="display:none;">
                        <label class="form-label">Outro problema</label>
                        <?= Html::textInput(
                            'otherProblem',
                            '',
                            [
                                'class' => 'form-control',
                                'placeholder' => 'Descreva o problema'
                            ]
                        ) ?>
                    </div>

                    <?= $form->field($addReport, 'desc')->textarea(['rows' => 4]) ?>

                    <div class="d-flex justify-content-end">
                        <?= Html::submitButton('Criar Relatório', ['class'=>'btn btn-danger']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        <?php endif; ?>

        <!-- DETAIL PANEL -->
        <?php if ($detailReport): ?>
            <div id="detailPanel" class="detail-panel show bg-white shadow">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="fw-bold">Detalhes do Relatório</h5>
                        <button class="btn btn-sm btn-light" onclick="closePanels()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php if ($isTechnician): ?>
                        <?php $form = ActiveForm::begin([
                            'action' => ['report/update', 'id' => $detailReport->id],
                            'method' => 'post'
                        ]); ?>
                    <?php endif; ?>

                    <?= $isTechnician
                        ? $form->field($detailReport, 'problemType')
                        : Html::input('text', null, $detailReport->problemType,
                            ['class'=>'form-control mb-3', 'readonly'=>true])
                    ?>

                    <?= $isTechnician
                        ? $form->field($detailReport, 'desc')->textarea(['rows' => 4])
                        : Html::textarea(null, $detailReport->desc,
                            ['class'=>'form-control', 'readonly'=>true])
                    ?>

                    <?php if ($isTechnician): ?>
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-light" onclick="closePanels()">Fechar</button>
                            <?= Html::submitButton('Salvar', ['class'=>'btn btn-danger']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    <?php endif; ?>

                </div>
            </div>

            <script>
                function closePanels() {
                    window.location.href = '<?= Url::to(['report/index']) ?>';
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('problem-type-select');
            const wrapper = document.getElementById('other-problem-wrapper');

            if (!select || !wrapper) return;

            select.addEventListener('change', () => {
                wrapper.style.display = (select.value === 'Outro') ? 'block' : 'none';
            });
        });
    </script>
</div>
