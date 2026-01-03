<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Extras';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
?>
<div class="content">
    <div class="container-fluid py-4" style="background-color:#f9fafb; min-height:100vh;">
        <?php Pjax::begin([
                'id' => 'metersTable',
                'timeout' => 5000,
                'enablePushState' => false, // important
        ]); ?>
        <!-- NAVIGATION? -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
            <h4 class="fw-bold text-dark">Extras</h4>
            <div class="d-flex align-items-center gap-3">
                <div class="input-group mx-5" style="width:220px;">
                    <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['index'],
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
                <button class="btn btn-primary"
                        data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Adicionar Tipo de Contador
                </button>
            </div>
        </div>
        <!-- ALERT -->
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <?php
            $bgClass = match($type) {
                'error' => 'bg-danger text-white',
                'success' => 'bg-success text-white',
                default => 'bg-info text-white',
            };
            ?>
            <div class="toast show <?= $bgClass ?> ms-auto" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-bell-fill me-2"></i><?= $message ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- METERTYPES LIST -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">
                <h6 class="fw-bold text-secondary mb-3">
                    Total de Tipo de Contadores: <?= count($meterTypes) ?>
                </h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($meterTypes)): ?>
                            <?php foreach ($meterTypes as $meterType): ?>
                                <tr>
                                    <td><?= htmlspecialchars($meterType->id) ?></td>
                                    <td><?= htmlspecialchars($meterType->description) ?></td>
                                    <td>
                                        <?= Html::button('Ver Detalhes', [
                                                'class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm',
                                                'onclick' => "window.location.href='" . Url::to(['extras/index', 'id' => $meterType->id]) . "'",
                                                'style' => 'transition: all 0.2s ease-in-out;',
                                                'onmouseover' => "this.style.transform='scale(1.05)';",
                                                'onmouseout' => "this.style.transform='scale(1)';"
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Nenhum extra encontrado.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php Pjax::end(); ?>
        <!-- RIGHT ADD PANEL -->
        <div id="rightPanel" class="right-panel bg-white shadow" style="display:none;">
            <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Adicionar Tipo de Contador</h5>
                <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-3">
                <?php $form = \yii\widgets\ActiveForm::begin([
                        'id' => 'add-extra-form',
                        'action' => ['extras/create'],
                        'method' => 'post',
                ]); ?>
                <?= $form->field($addMeterTypeModel, 'description')->textInput(['placeholder' => 'Descrição', 'autofocus' => true]) ?>
                <div class="text-end mt-3">
                    <?= \yii\helpers\Html::submitButton('Criar Tipo de Contador', ['class' => 'btn btn-primary', 'name' => 'create-extra-button']) ?>
                </div>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>
        <!-- DETAIL PANEL -->
        <?php if ($detailMeterTypes): ?>
            <div id="detailPanel" class="detail-panel bg-white shadow show">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4" style="background-color:#fff">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Tipo de Contador</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'update-meter-type-form',
                            'action' => ['update', 'id' => $detailMeterTypes->id],
                            'method' => 'post',
                    ]); ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <?= $form->field($detailMeterTypes, 'id')->textInput(['readonly' => true])->label('ID') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($detailMeterTypes, 'description')->textInput()->label('Descrição') ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <button type="button" class="closeDetailPanel btn btn-light px-4 py-2">Fechar</button>
                        <?= \yii\helpers\Html::submitButton('Salvar', [
                                'class' => 'btn btn-primary px-4 py-2',
                                'style' => 'background-color:#4f46e5; border:none;'
                        ]) ?>
                        <?php \yii\widgets\ActiveForm::end(); ?>

                        <?= Html::beginForm(['extras/delete', 'id' => $detailMeterTypes->id], 'post', [
                                'onsubmit' => 'return confirm("Tem a certeza que quer eliminar este Tipo de Contador?");',
                                'class' => 'mb-0'
                        ]) ?>
                        <?= Html::submitButton('<i class="fas fa-trash"></i>', [
                                'class' => 'btn btn-danger px-4 py-2',
                                'title' => 'Eliminar'
                        ]) ?>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--ATIVAR O DETAIL PANEL -->
        <?php if ($detailMeterTypes): ?>
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
