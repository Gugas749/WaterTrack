<?php
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\models\Enterprise;
use common\models\Userprofile;
use yii\widgets\Pjax;

$this->title = 'Utilizadores';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/views-index.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('@web/js/main-index.js', ['depends' => [\yii\bootstrap5\BootstrapPluginAsset::class]]);
$this->registerJsFile('@web/js/user-index-form.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>
<?php
$technicianRole = [
        '0' => 'Morador',
        '1' => 'Técnico'
];
$statusOptions = [
        10 => 'ATIVO',
        9  => 'INATIVO',
        0  => 'DESATIVADO',
];
$statusClasses = [
        10 => 'text-success',
    9  => 'text-warning',
    0  => 'text-danger',
];

$statusClassesUser = match ($user->status ?? null) {
    10 => 'bg-success',
    9  => 'bg-warning',
    0  => 'bg-danger',
    default => 'bg-secondary',
};

$statusText = match ($user->status ?? null) {
    10 => 'ATIVO',
    9  => 'INATIVO',
    0  => 'DESATIVADO',
    default => 'DESCONHECIDO',
};

$statusClass = match ($user->status ?? null) {
    10 => 'text-success',
    9  => 'text-warning',
    0  => 'text-danger',
    default => 'text-muted',
};
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
            <h4 class="fw-bold text-dark">Utilizadores</h4>
            <div class="d-flex align-items-center gap-3">
                <!-- Search -->
                <div class="input-group mx-5" style="width:220px;">
                    <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['user/index'],
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
                <button class="btn btn-primary"
                        data-toggle="right-panel"
                        style="background-color:#4f46e5; border:none;">
                    <i class="fas fa-plus me-1"></i> Adicionar Utilizador
                </button>
            </div>
        </div>
        <!-- USER LIST -->
        <div class="card shadow-sm border-0 mx-3" style="border-radius:16px;">
            <div class="card-body">
                <h6 class="fw-bold text-secondary mb-3">
                    Total de Utilizadores: <?= count($users) ?>
                </h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                        <tr>
                            <th>Referência</th>
                            <th>Nome</th>
                            <th>Morada</th>
                            <th>Tipo de Utilizador</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user->id) ?></td>
                                    <td>
                                        <?= htmlspecialchars($user->username) ?>
                                    </td>
                                    <td><?= $user->userprofile && $user->userprofile->address ? htmlspecialchars($user->userprofile->address) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $enterpriseText = $user->technicianinfos === null ? 'Morador' : 'Técnico';
                                        ?>
                                        <?= htmlspecialchars($enterpriseText) ?>
                                    </td>
                                    <td>
                                        <?php $form = \yii\widgets\ActiveForm::begin([
                                                'id' => 'update-userstatus-form',
                                                'action' => ['update-status', 'id' => $user->id],
                                                'method' => 'post',
                                        ]);

                                        $statusClassUser = $statusClasses[$user->status] ?? 'text-muted';
                                        ?>

                                        <?= Html::dropDownList('status', $user->status, [
                                                10 => 'ATIVO',
                                                9  => 'INATIVO',
                                                0  => 'DESATIVADO',
                                        ], [
                                                'class' => 'form-select form-select-sm fw-bold '. $statusClassUser,
                                                'id' => 'user-status-table-dropdown-' . $user->id,
                                                'data-user-id' => $user->id,
                                                'onchange' => 'this.form.submit();',
                                                'options' => [
                                                        10 => ['class' => 'text-success'],
                                                        9 => ['class' => 'text-warning'],
                                                        0 => ['class' => 'text-danger'],
                                                ]
                                        ]) ?>
                                        <?php \yii\widgets\ActiveForm::end(); ?>
                                    </td>
                                    <td>
                                        <?= Html::button('Ver Detalhes', [
                                                'class' => 'btn btn-outline-primary btn-sm fw-semibold shadow-sm',
                                                'onclick' => "window.location.href='" . Url::to(['user/index', 'id' => $user->id]) . "'",
                                                'style' => 'transition: all 0.2s ease-in-out;',
                                                'onmouseover' => "this.style.transform='scale(1.05)';",
                                                'onmouseout' => "this.style.transform='scale(1)';"
                                        ]) ?>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Nenhum utilizador encontrado.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php Pjax::end(); ?>

        <!-- RIGHT ADD PANEL -->
        <div id="rightPanel" class="right-panel bg-white shadow" style="display:none; position:fixed; top:0; right:0; width:400px; height:100%; z-index:1050; overflow-y:auto;">
            <div class="right-panel-header d-flex justify-content-between align-items-center p-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">Adicionar Utilizador</h5>
                <button type="button" class="btn btn-sm btn-light" id="closeRightPanel">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-3">
                <?php $form = \yii\widgets\ActiveForm::begin([
                        'id' => 'add-user-form',
                        'action' => ['user/create'],
                        'method' => 'post',
                ]); ?>

                <?= $form->field($addUserModel, 'username')->textInput(['placeholder' => 'Username']) ?>
                <?= $form->field($addUserModel, 'email')->textInput(['placeholder' => 'Email']) ?>
                <?= $form->field($addUserModel, 'password')->passwordInput(['placeholder' => 'Password']) ?>

                <?= $form->field($addUserModel, 'name')->textInput(['placeholder' => 'Nome']) ?>
                <?= $form->field($addUserModel, 'address')->textInput(['placeholder' => 'Morada']) ?>
                <?= $form->field($addUserModel, 'birthDate')->input('date') ?>

                <?php
                $isTechnician = $addUserModel->technicianFlag == '1';
                ?>
                <?= $form->field($addUserModel, 'technicianFlag')->dropDownList([
                        '0' => 'Morador',
                        '1' => 'Técnico',
                ], ['id' => 'create-user-type']) ?>

                <div id="technician-extra" class="hidden">
                    <?= $form->field($addUserModel, 'enterpriseID')->dropDownList(
                            ArrayHelper::map($enterpriseList, 'id', 'name'),
                            ['prompt' => 'Selecione a empresa']
                    ) ?>
                    <?= $form->field($addUserModel, 'profissionalCertificateNumber')->textInput(['placeholder' => 'Nº Certificado Profissional']) ?>
                </div>

                <div class="text-end mt-3">
                    <?= \yii\helpers\Html::submitButton('Criar Utilizador', ['class' => 'btn btn-primary', 'name' => 'createuser-button']) ?>
                </div>

                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>
        <!-- DETAIL PANEL -->
        <?php if ($detailUser): ?>
            <div id="detailPanel" class="detail-panel bg-white shadow show">
                <div class="modal-content border-0 shadow-lg rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Detalhes do Utilizador</h5>
                        <button type="button" class="closeDetailPanel btn btn-sm btn-light">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <?php $form = \yii\widgets\ActiveForm::begin([
                            'id' => 'update-user-form',
                            'action' => ['update', 'id' => $detailUser->id],
                            'method' => 'post',
                    ]); ?>

                    <!-- STATUS BADGE -->
                    <div class="mb-4">
                        <?php
                        $statusClass = $statusClasses[$detailUser->status ?? 10] ?? 'bg-secondary';
                        $statusText = $statusOptions[$detailUser->status ?? 10] ?? 'DESCONHECIDO';
                        ?>
                        <span id="user-status-badge" class="badge <?= $statusClass ?> px-3 py-2"><?= $statusText ?></span>
                    </div>

                    <?php
                    $profile = $detailUser->userprofile ?? new Userprofile();
                    $techInfo = null;
                    $isTechnician = false;
                    if($detailUser->technicianinfos !== null){
                        $techInfo = $detailUser->technicianinfos;
                        $isTechnician = true;
                    }
                    $enterpriseList = ArrayHelper::map(Enterprise::find()->all(), 'id', 'name');
                    $selectedValue = $isTechnician ? '1' : '0';
                    ?>

                    <!-- TECNICO STUFF -->
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-4">
                            <?= $form->field($detailUser, 'technicianinfos')->dropDownList(
                                    ['0' => 'Morador', '1' => 'Técnico'],
                                    ['options' => [$selectedValue => ['Selected' => true]], 'id' => 'user-type-dropdown']
                            )->label('Tipo de Utilizador') ?>
                        </div>
                        <?php
                        $techInfoModel = new \common\models\Technicianinfo();
                        if($isTechnician){
                            $techInfoModel = $techInfo;
                        }
                        ?>

                        <div class="col-md-4 professional-field <?= $isTechnician ? '' : 'hidden' ?>">
                            <?= $form->field($techInfoModel, 'enterpriseID')->dropDownList(
                                    $enterpriseList,
                                    ['prompt' => 'Selecione a empresa']
                            )->label('Empresa Associada') ?>
                        </div>

                        <div class="col-md-4 professional-field <?= $isTechnician ? '' : 'hidden' ?>">
                            <?= $form->field($techInfoModel, 'profissionalCertificateNumber')->textInput()->label('Nº Certificado Profissional') ?>
                        </div>

                    </div>

                    <!-- RESTO DOS CAMPOS -->
                    <div class="row g-1">
                        <div class="col-md-2"><?= $form->field($detailUser, 'id')->textInput(['readonly' => true])->label('Referência') ?></div>
                        <div class="col-md-4"><?= $form->field($profile, 'name')->textInput(['value' => $profile->name ?? 'N/A'])->label('Nome') ?></div>
                        <div class="col-md-3"><?= $form->field($detailUser, 'username')->textInput()->label('Username') ?></div>
                        <div class="col-md-4"><?= $form->field($profile, 'birthDate')->input('date', ['value' => $profile->birthDate ? date('Y-m-d', strtotime($profile->birthDate)) : null])->label('Data de Nascimento') ?></div>
                        <div class="col-md-6"><?= $form->field($detailUser, 'email')->textInput()->label('Email') ?></div>
                        <div class="col-md-6"><?= $form->field($profile, 'address')->textInput(['value' => $profile->address ?? 'N/A'])->label('Morada') ?></div>
                        <div class="col-md-4"><?= $form->field($detailUser, 'status')->dropDownList($statusOptions, ['id' => 'user-status-dropdown'])->label('Estado') ?></div>
                        <div class="col-md-5"><?= $form->field($detailUser, 'created_at')->textInput(['value' => Yii::$app->formatter->asDate($detailUser->created_at), 'readonly' => true])->label('Data de Registo') ?></div>
                        <div class="col-md-5"><?= $form->field($detailUser, 'updated_at')->textInput(['value' => Yii::$app->formatter->asDate($detailUser->updated_at), 'readonly' => true])->label('Última Atualização') ?></div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <button type="button" class="closeDetailPanel btn btn-light px-4">Fechar</button>
                        <?= Html::submitButton('Editar', ['class' => 'btn btn-primary px-4 py-2', 'style' => 'background-color:#4f46e5; border:none;']) ?>
                        <?php \yii\widgets\ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!--ATIVAR O DETAIL PANEL -->
        <?php if ($detailUser): ?>
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

<script>
    document.addEventListener('click', function(event) {
        const target = event.target;

        // Abrir Right Panel
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
                z-index:1049;
                display:none;
            `;
                document.body.appendChild(overlay);
            }

            panel.style.display = 'block';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
            return;
        }

        // Fechar Right Panel ao clicar no botão ou no overlay
        if (target.closest('#closeRightPanel') || target.closest('#overlay')) {
            const panel = document.getElementById('rightPanel');
            const overlay = document.getElementById('overlay');

            if (panel) panel.style.display = 'none';
            if (overlay) overlay.style.display = 'none';
            document.body.style.overflow = 'auto';
            return;
        }
    });
</script>
