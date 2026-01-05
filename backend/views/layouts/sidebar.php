<?php
use yii\bootstrap5\Html;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a class="brand-link">
        <img src="../../web/img/logo_1.png" alt="Watertrack Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">WaterTrack</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column justify-content-between">

        <!-- Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                    'options' => [
                            'class' => 'nav nav-pills nav-sidebar flex-column nav-legacy',
                            'data-widget' => 'treeview',
                            'role' => 'menu'
                    ],
                    'items' => [
                            ['label' => 'Dashboard', 'icon' => 'home', 'url' => ['dashboard/index']],
                            ['label' => 'Utilizadores', 'icon' => 'user', 'url' => ['user/index']],
                            ['label' => 'Contadores', 'icon' => 'tint', 'url' => ['meter/index']],
                            ['label' => 'Leituras', 'icon' => 'book-open', 'url' => ['reading/index']],
                            ['label' => 'Reports', 'icon' => 'bug', 'url' => ['report/index']],
                            ['label' => 'Empresas', 'icon' => 'building', 'url' => ['enterprise/index']],
                            ['label' => 'Extras', 'icon' => 'cube', 'url' => ['extras/index']],
                            ['label' => 'Definições', 'icon' => 'cog', 'url' => ['settings/index']],
                    ],
            ]);
            ?>
        </nav>


        <!-- Painel do utilizador fixo ao fundo -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center border-top pt-3">
            <div class="image d-flex align-items-center justify-content-center">
                <i class="bi bi-person-circle text-secondary"
                   style="font-size: 1.2rem;"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= Html::encode(Yii::$app->user->identity->username) ?></a>
            </div>
        </div>
    </div>
</aside>

