<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

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
    <?php Pjax::begin([
            'id' => 'reportsTable',
            'timeout' => 5000,
            'enablePushState' => false, // important
    ]); ?>
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <h4 class="fw-bold">Relatórios</h4>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <!-- SEARCH -->
            <div class="input-group" style="width:220px;">
            <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'action' => ['report/index'],
                    'options' => ['data' => ['pjax' => true], 'class' => 'd-flex align-items-center w-100'],
            ]); ?>
                <div>
                    <input type="text" name="q"
                           class="form-control form-control-sm ps-3 pe-5 MX-2"
                           placeholder="Search"
                           value="<?= Html::encode($search) ?>"
                           style="border:1px solid #e5e7eb;">
                    <button type="submit" class="input-group-text bg-transparent border-0 text-muted"
                            style="position:absolute; right:10px; top:50%; transform:translateY(-50%);">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

            <?php ActiveForm::end(); ?>
            </div>
        </div>
        <!-- Open Panel Button -->
        <button class="btn btn-danger ms-4" data-toggle="right-panel" style="background-color:#4f46e5; border:none;">
            <i class="fas fa-plus me-1"></i> Novo Relatório
        </button>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table align-middle">
                <thead class="text-muted">
                <tr>
                    <th>Referência</th>
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
                            <td><?= Html::encode($report->id) ?></td>
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


    <script>
        document.addEventListener('click', function(event) {
            const target = event.target;

            // --- Abrir Right Panel (Novo Relatório) ---
            if (target.closest('[data-toggle="right-panel"]')) {
                const panel = document.getElementById('rightPanel');
                if (!panel) return;

                let overlay = document.getElementById('overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'overlay';
                    overlay.style.cssText = `
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                z-index:999;
                display:none;
            `;
                    document.body.appendChild(overlay);
                }

                panel.style.display = 'block';
                panel.style.position = 'fixed';
                panel.style.top = '0';
                panel.style.right = '0';
                panel.style.height = '100%';
                panel.style.zIndex = '1050';
                panel.style.backgroundColor = '#fff';

                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
                return;
            }

            // --- Abrir Detail Panel (quando $detailProblem definido) ---
            if (target.closest('.openDetailPanel')) {
                const panel = document.getElementById('detailPanel');
                if (!panel) return;

                let overlay = document.getElementById('overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'overlay';
                    overlay.style.cssText = `
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                z-index:999;
                display:none;
            `;
                    document.body.appendChild(overlay);
                }

                panel.style.display = 'block';
                panel.style.position = 'fixed';
                panel.style.top = '50%';
                panel.style.left = '50%';
                panel.style.transform = 'translate(-50%, -50%)';
                panel.style.zIndex = '1050';

                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
                return;
            }

            // --- Fechar qualquer painel via overlay ou botão fechar ---
            if (target.closest('#closeRightPanel') || target.closest('.closeDetailPanel') || target.closest('#overlay')) {
                const rightPanel = document.getElementById('rightPanel');
                const detailPanel = document.getElementById('detailPanel');
                const overlay = document.getElementById('overlay');

                if (rightPanel) rightPanel.style.display = 'none';
                if (detailPanel) detailPanel.style.display = 'none';
                if (overlay) overlay.style.display = 'none';
                document.body.style.overflow = '';
                return;
            }
        });

        // --- Se já existir $detailProblem, mostrar overlay e detailPanel automaticamente ---
        document.addEventListener('DOMContentLoaded', () => {
            const detail = document.getElementById('detailPanel');
            if (detail) {
                detail.style.display = 'block';

                let overlay = document.getElementById('overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.id = 'overlay';
                    overlay.style.cssText = `
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                z-index:999;
            `;
                    document.body.appendChild(overlay);
                }
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    </script>


</div>
