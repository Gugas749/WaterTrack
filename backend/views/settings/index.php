<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'Configurações';
?>

<div class="container my-5">
    <div class="row g-4">

        <!-- COLUNA ESQUERDA: Perfil -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4">

                <h5 class="mb-4 fw-semibold">Minhas Informações</h5>

                <div class="text-center mb-4">
                    <div class="profile-avatar mx-auto">
                        <i class="bi bi-person-circle" style="font-size:60px;"></i>
                    </div>
                </div>

                <?php $form = ActiveForm::begin([
                        'id' => 'profile-form',
                        'options' => ['autocomplete' => 'off'],
                        'action' => ['settings/update'],
                        'method' => 'post',
                ]); ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $form->field($user, 'id')->textInput(['disabled' => true])->label('Cliente ID') ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $form->field($profile, 'birthDate')->input('date')->label('Data de Nascimento') ?>
                    </div>
                </div>

                <?= $form->field($profile, 'name')->textInput()->label('Nome') ?>
                <?= $form->field($user, 'email')->input('email')->label('Email') ?>
                <?= $form->field($profile, 'address')->textInput()->label('Morada') ?>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-primary" id="openPasswordPanel">Resetar Password</button>
                    <?= Html::submitButton('Guardar Alterações', ['class' => 'btn btn-primary px-4']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- COLUNA DIREITA: Configurações adicionais -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                <h5 class="mb-4 fw-semibold">Configurações Adicionais</h5>
                <p class="text-center text-muted">
                    De momento, sem configurações adicionais.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- DETAIL PANEL: Reset Password -->
<div id="passwordPanel" class="detail-panel bg-white shadow" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:400px; z-index:1050; border-radius:16px;">
    <div class="modal-content border-0 rounded-4 p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">Redefinir Password</h5>
            <button type="button" class="btn btn-sm btn-light" id="closePasswordPanel">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <?php $passwordForm = ActiveForm::begin([
                'id' => 'reset-password-form',
                'action' => ['settings/reset-password'],
                'method' => 'post'
        ]); ?>

        <?= $passwordForm->field($passwordModel, 'oldPassword')->passwordInput()->label('Password Antiga') ?>
        <?= $passwordForm->field($passwordModel, 'newPassword')->passwordInput()->label('Nova Password') ?>
        <?= $passwordForm->field($passwordModel, 'newPasswordRepeat')->passwordInput()->label('Repetir Nova Password') ?>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light px-4" id="cancelPasswordPanel">Cancelar</button>
            <?= Html::submitButton('Alterar', ['class' => 'btn btn-primary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- OVERLAY -->
<div id="overlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('openPasswordPanel');
        const passwordPanel = document.getElementById('passwordPanel');
        const overlay = document.getElementById('overlay');
        const closeBtn = document.getElementById('closePasswordPanel');
        const cancelBtn = document.getElementById('cancelPasswordPanel');

        openBtn.addEventListener('click', () => {
            passwordPanel.style.display = 'block';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });

        function closePanel() {
            passwordPanel.style.display = 'none';
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        closeBtn.addEventListener('click', closePanel);
        cancelBtn.addEventListener('click', closePanel);
    });
</script>
