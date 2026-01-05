<?php
// Helpers do Yii
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

// Título da página
$this->title = 'Relatórios';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

// Estados dos problemas
$problemStates = [
        0 => 'RESOLVIDO',
        1 => 'EM ANÁLISE',
        2 => 'POR RESOLVER',
];

// Classes CSS para cada estado
$stateClasses = [
        0 => 'text-success',
        1 => 'text-warning',
        2 => 'text-danger',
];
?>

<div class="container-fluid py-4 position-relative">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Relatórios</h4>
        <button class="btn btn-danger" data-toggle="right-panel" style="background-color:#4f46e5; border:none;">
            <i class="fas fa-plus me-1"></i> Novo Relatório
        </button>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table align-middle">
                <thead class="text-muted">
                <tr>
                    <th>Nº Relatório</th>
                    <th>Nº Contador</th>
                    <th>Morada</th>
                    <th>Criado por</th>
                    <th>Técnico Atribuído</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($reports)): ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>Relatório nº<?= Html::encode($report->id) ?></td>
                            <td>Contador nº<?= Html::encode($report->meter->id) ?><br></td>
                            <td><?= Html::encode($report->meter->address) ?></td>
                            <td><?= Html::encode($report->user->username) ?></td>
                            <td>
                                <?= $report->tecnico ? Html::encode($report->tecnico->username) : '<span class="text-muted">Não atribuído</span>' ?>
                            </td>
                            <td class="fw-bold <?= $stateClasses[$report->problemState] ?>">
                                <?= $problemStates[$report->problemState] ?>
                            </td>
                            <td>
                                <?= Html::button('Ver Detalhes', [
                                        'class' => 'btn btn-outline-primary btn-sm',
                                        'onclick' => "window.location.href='" . Url::to(['report/index', 'id' => $report->id]) . "'"
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhum relatório encontrado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Panel -->
    <div id="rightPanel" class="position-fixed top-0 end-0 bg-white shadow" style="width:400px; height:100%; z-index:1050; display:none; overflow-y:auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h5 class="fw-bold">Criar Relatório</h5>
            <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-3">
            <?php $form = ActiveForm::begin([
                    'action' => ['report/create'],
                    'method' => 'post',
            ]); ?>

            <h6 class="fw-bold text-dark m-2">Nº do Contador</h6>
            <?= $form->field($model, 'meterID')->dropDownList(
                    ArrayHelper::map($meters, 'id', fn($m) => $m->id . ' - ' . $m->address),
                    ['prompt' => 'Selecione o contador']
            )->label(false) ?>

            <h6 class="fw-bold text-dark m-2">Descrição</h6>
            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Indique o problema'])->label(false) ?>

            <div class="text-center mt-3">
                <?= Html::submitButton('Criar Relatório', ['class' => 'btn btn-danger', 'style' => 'background-color:#4f46e5; border:none;']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Detail Panel -->
    <?php if ($detailProblem): ?>
        <?php
        $stateBadgeClasses = [
                0 => 'bg-success',
                1 => 'bg-warning',
                2 => 'bg-danger',
        ];

        $dropdownDisabled = !$detailProblem->tecnicoID || $detailProblem->tecnicoID != Yii::$app->user->id;
        ?>

        <?php $form = ActiveForm::begin([
                'action' => ['report/update', 'id' => $detailProblem->id],
                'method' => 'post',
        ]); ?>

        <div id="detailPanel" class="position-fixed top-50 start-50 translate-middle bg-white shadow-lg rounded-4 p-4" style="z-index:1050; width:500px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Detalhes do Relatório</h5>
                <button type="button" class="btn btn-sm btn-light closeDetailPanel">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <span class="badge <?= $stateBadgeClasses[$detailProblem->problemState] ?> mb-3">
                <?= $problemStates[$detailProblem->problemState] ?>
            </span>

            <h6 class="fw-bold text-dark m-2">Descrição</h6>
            <?= $form->field($detailProblem, 'description')->textarea([
                    'disabled' => $dropdownDisabled,
                    'rows' => 2,
            ])->label(false) ?>

            <div class="mb-3">
                <h6 class="fw-bold text-dark m-2">Nº do Contador</h6>
                <input class="form-control" type="text"
                       value="<?= $detailProblem->meter->id ?> - <?= $detailProblem->meter->address ?>"
                       disabled>

                <!-- Hidden para enviar o meterID correto -->
                <?= $form->field($detailProblem, 'meterID')->hiddenInput()->label(false) ?>
            </div>

            <?php if ($isTechnician): ?>
                <h6 class="fw-bold text-dark m-2">Estado do Relatório</h6>
                <?= $form->field($detailProblem, 'problemState')->dropDownList($problemStates, [
                        'disabled' => $dropdownDisabled,
                ])->label(false) ?>
            <?php endif; ?>

            <div class="d-flex justify-content-end mt-3 gap-2">
                <button type="button" class="btn btn-light closeDetailPanel">Fechar</button>

                <?php if ($isTechnician): ?>
                    <?php if (!$detailProblem->tecnicoID): ?>
                        <?= Html::submitButton('Assumir Análise', [
                                'class' => 'btn btn-danger',
                                'style' => 'background-color:#4f46e5; border:none;',
                                'name' => 'action',
                                'value' => 'assign'
                        ]) ?>
                    <?php elseif ($detailProblem->tecnicoID == Yii::$app->user->id): ?>
                        <?= Html::submitButton('Atualizar Relatório', [
                                'class' => 'btn btn-success',
                                'style' => 'background-color:#4f46e5; border:none;',
                                'name' => 'action',
                                'value' => 'update'
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <div id="overlay"></div>


    <!-- Scripts -->
    <script>
        const overlay = document.getElementById('overlay');
        const rightPanel = document.getElementById('rightPanel');
        const openBtn = document.querySelector('[data-toggle="right-panel"]');
        const closeBtn = document.getElementById('closeRightPanel');

        if (openBtn) openBtn.onclick = () => { rightPanel.style.display='block'; overlay.style.display='block'; document.body.style.overflow='hidden'; };
        if (closeBtn) closeBtn.onclick = () => { rightPanel.style.display='none'; overlay.style.display='none'; document.body.style.overflow='auto'; };
        overlay.onclick = () => { if (rightPanel.style.display==='block') { rightPanel.style.display='none'; overlay.style.display='none'; document.body.style.overflow='auto'; } };

        const closeDetailButtons = document.querySelectorAll('.closeDetailPanel');
        closeDetailButtons.forEach(btn => btn.onclick = () => window.location.href='<?= Url::to(['report/index']) ?>');

        if (document.getElementById('detailPanel')) { overlay.style.display='block'; document.body.style.overflow='hidden'; }
    </script>

</div>
