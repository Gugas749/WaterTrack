<?php
use frontend\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\helpers\Url;

AppAsset::register($this);
$this->registerCsrfMetaTags();
$this->title = $this->title ?: 'WaterTrack';

$route = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

$showSidebar = !in_array($route, [
        'site/login',
        'site/signup',
        'site/request-password-reset',
        'site/reset-password'
]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php if ($showSidebar): ?>
    <div class="sidebar">
        <div class="logo">
            <?= Html::a('💧 WaterTrack', Url::to(['/dashboard/index'])) ?>
        </div>

        <?= Nav::widget([
                'options' => ['class' => 'nav flex-column'],
                'items' => [
                        ['label' => '📊 Dashboard', 'url' => ['/dashboard/index']],
                        ['label' => '🧮 Contadores', 'url' => ['/meter/index']],
                        ['label' => '📖 Leituras', 'url' => ['/reading/index']],
                        ['label' => '📈 Relatório', 'url' => ['/report/index']],
                        ['label' => '⚙️ Definições', 'url' => ['/settings/index']],
                ],
                'encodeLabels' => false
        ]) ?>


        <div style="position:absolute; bottom:20px; width:100%; text-align:center;">

            <?php if (!Yii::$app->user->isGuest): ?>
                <div style="margin-bottom:10px; font-size:14px;">
                    👤 <?= Html::encode(Yii::$app->user->identity->username) ?>
                </div>

                <?= Html::a('🚪 Logout', ['/site/logout'], [
                        'class' => 'nav-link',
                        'data-method' => 'post',
                        'style' => 'font-size:14px; display:block;'
                ]) ?>
            <?php else: ?>
                <?= Html::a('🔐 Login', ['/site/login'], [
                        'class' => 'nav-link',
                        'style' => 'font-size:14px;'
                ]) ?>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<div class="main-content" style="<?= $showSidebar ? '' : 'margin-left:0; max-width:600px; margin:auto; padding-top:80px;' ?>">
    <?php if ($showSidebar): ?>
    <?php endif; ?>

    <?= $content ?>

    <?php if ($showSidebar): ?>
        <footer>
            &copy; WaterTrack <?= date('Y') ?> | Powered by Yii2
        </footer>
    <?php endif; ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
