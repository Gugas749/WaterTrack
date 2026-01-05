<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

$this->title = 'Relatórios';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);

$problemStates = [
        0 => 'RESOLVIDO',
        1 => 'EM ANÁLISE',
        2 => 'POR RESOLVER',
];

$stateClasses = [
        0 => 'text-success',
        1 => 'text-warning',
        2 => 'text-danger',
];
?>

<div class="container-fluid py-4 position-relative">
    <?php Pjax::begin([
            'id' => 'reportsTable',
            'timeout' => 5000,
            'enablePushState' => false, // important
    ]); ?>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Relatórios</h4>
        <div class="d-flex align-items-center gap-3">
            <!-- Search -->
            <div class="input-group mx-5" style="width:220px;">
                <?php $form = ActiveForm::begin([
                        'method' => 'get',
                        'action' => ['report/index'],
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
            <!-- Open Panel Button -->
            <button class="btn btn-primary" data-toggle="right-panel">
                <i class="fas fa-plus me-1"></i> Novo Relatório
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
                            <td>Contador nº<?= Html::encode($report->meter->id) ?></td>
                            <td><?= Html::encode($report->meter->address) ?></td>
                            <td><?= Html::encode($report->user->username) ?></td>
                            <td>
                                <?= $report->tecnico ? Html::encode($report->tecnico->username) : '<span class="text-muted">Não atribuído</span>' ?>
                            </td>
                            <td class="<?= $stateClasses[$report->problemState] ?> fw-bold">
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
    <?php Pjax::end(); ?>
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

            <h6 class="fw-bold m-2">Nº do Contador</h6>
            <?= $form->field($model, 'meterID')->dropDownList(
                    ArrayHelper::map($meters, 'id', fn($m) => $m->id . ' - ' . $m->address),
                    ['prompt' => 'Selecione o contador']
            )->label(false) ?>

            <h6 class="fw-bold m-2">Descrição</h6>
            <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Indique o problema'])->label(false) ?>

            <div class="text-center mt-3">
                <?= Html::submitButton('Criar Relatório', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

    <!-- Detail Panel -->
    <?php if ($detailProblem): ?>
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

            <span class="badge <?= $stateClasses[$detailProblem->problemState] ?> mb-3">
                <?= $problemStates[$detailProblem->problemState] ?>
            </span>
            <div class="mb-3">
                <h6 class="fw-bold m-2">Nº do Contador</h6>
                <input class="form-control" type="text" value="<?= $detailProblem->meter->id ?> - <?= $detailProblem->meter->address ?>" disabled>
                <?= $form->field($detailProblem, 'meterID')->hiddenInput()->label(false) ?>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold m-2">Empresa Atribuída</h6>
                <input class="form-control" type="text" value="<?= $detailsEnterprise->name ?> - <?= $detailsEnterprise->address ?>" disabled>
            </div>

            <h6 class="fw-bold m-2">Técnico Atribuído</h6>
            <?= $form->field($detailProblem, 'tecnicoID')->dropDownList(
                    ArrayHelper::map($detailsTechnicians, 'id', fn($t) => $t->id . ' - ' . $t->username),
                    ['prompt' => 'Nenhum']
            )->label(false) ?>

            <!-- ESTADO DO RELATÓRIO -->
            <h6 class="fw-bold text-secondary mt-3 mb-2">Estado do Relatório</h6>
            <?= $form->field($detailProblem, 'problemState')->dropDownList([
                    0 => 'RESOLVIDO',
                    1 => 'EM ANÁLISE',
                    2 => 'POR RESOLVER',
            ], [
                    'class' => 'form-select',
            ])->label(false) ?>

            <h6 class="fw-bold m-2">Descrição</h6>
            <?= $form->field($detailProblem, 'description')->textarea(['rows' => 2])->label(false) ?>

            <div class="d-flex justify-content-end mt-3 gap-2">
                <button type="button" class="btn btn-light closeDetailPanel">Fechar</button>
                <?= Html::submitButton('Atualizar Relatório', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>

    <!-- Overlay -->
    <div id="overlay"></div>
</div>

<script>
    const overlay = document.getElementById('overlay');
    const rightPanel = document.getElementById('rightPanel');
    const openBtn = document.querySelector('[data-toggle="right-panel"]');
    const closeBtn = document.getElementById('closeRightPanel');

    if(openBtn) openBtn.onclick = () => { rightPanel.style.display='block'; overlay.style.display='block'; document.body.style.overflow='hidden'; };
    if(closeBtn) closeBtn.onclick = () => { rightPanel.style.display='none'; overlay.style.display='none'; document.body.style.overflow='auto'; };
    overlay.onclick = () => { if(rightPanel.style.display==='block'){ rightPanel.style.display='none'; overlay.style.display='none'; document.body.style.overflow='auto'; } };

    document.querySelectorAll('.closeDetailPanel').forEach(btn => btn.onclick = () => window.location.href='<?= Url::to(['report/index']) ?>');

    if(document.getElementById('detailPanel')) { overlay.style.display='block'; document.body.style.overflow='hidden'; }
</script>
