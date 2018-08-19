<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteAsset;
use app\assets\FontAwesomeAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use app\widgets\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use cebe\gravatar\Gravatar;

AdminLteAsset::register($this);
FontAwesomeAsset::register($this);

$this->registerCss('#lang-menu{
    overflow: auto;
    max-height: 200px;
}');

//List of language options
$languages = \app\models\Language::find()->all();
$langOpt = [];

if (!empty($languages)) {
    foreach ($languages as $lang) {
        //Check if the language is the active
        $active = ($lang->code == Yii::$app->language) ? 'active' : '';
        $langOpt[] = ['label'=>Yii::t('language', $lang->name_ascii), 'url'=>['site/change-language', 'lang'=>$lang->code], 'options'=>['class'=>$active]];
    }
}

$currentUrl = Yii::$app->controller->id.'/'.Yii::$app->controller->action->id;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" style="font-size: 14px">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name . ($this->title ? " - $this->title" : '')) ?></title>
    <?php $this->head() ?>
</head>
<body class="sidebar-mini sidebar-open">
<?php $this->beginBody() ?>
<div class="wrapper">
    <?php
    NavBar::begin([
        'options' => [
            'class' => 'main-header navbar navbar-expand bg-white navbar-light border-bottom',
        ],
    ]);

    $menuItemsLeft[] = ['label' => '', 'url' => '#', 'options'=>['class'=>'nav-item', 'data-widget'=>'pushmenu'], 'linkOptions'=>['class'=>'nav-link fa fa-bars']];
    $menuItemsRight[] = [
        'label' => Gravatar::widget([
                    'email' => Yii::$app->user->identity->email,
                    'options' => [
                        'alt' => 'Profile Gravatar',
                        'class' => 'img-circle',
                    ],
                    'size' => 20
                ]) . ' ' . Yii::$app->user->identity->email,
        'items' => [
            ['label' => Yii::t('app', 'Account'), 'url' => ['site/account'], 'linkOptions' => ['tabindex' => -1]],
            ['label' => Yii::t('app', 'Logout'), 'url' => ['site/logout'], 'linkOptions' => ['data-method' => 'post', 'tabindex' => -1]],
        ],
        'encode' => FALSE,
        'options' => ['class' => 'nav-item'],
        'linkOptions' => ['class' => 'nav-link'],
    ];
    $menuItemsRight[] = [
        'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-globe'])
            . (Yii::t('menu', 'Language')),
        'items' => $langOpt,
        'encode' => FALSE,
        'dropDownOptions' => ['id' => 'lang-menu'],
        'options' => ['class' => 'nav-item'],
        'linkOptions' => ['class' => 'nav-link'],
    ];

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => $menuItemsLeft,
        'dropDownCaret'=>'',
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ml-auto'],
        'items' => $menuItemsRight,
        'dropDownCaret'=>'',
    ]);
    NavBar::end();
    ?>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?=Yii::$app->homeUrl?>" class="brand-link">
            <span class="brand-abbr font-weight-light">OSW</span>
            <span class="brand-text font-weight-light">OSW</span>
        </a>

        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item has-treeview  <?= in_array($currentUrl, ['site/design-list', 'site/design-view', 'site/design-edit']) ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= in_array($currentUrl, ['site/design-list', 'site/design-view', 'site/design-edit']) ? 'active' : '' ?>">
                            <i class="nav-icon fa fa-edit"></i>
                            <p>Developer<i class="fa fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="<?= Yii::$app->urlManager->createUrl(['site/design-list']) ?>" class="nav-link <?= $currentUrl == 'site/design-list' ? 'active' : '' ?>">
                                    <i class="fa fa-circle-o nav-icon"></i>
                                    <p>Moqups</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= Yii::$app->urlManager->createUrl(['site/design-view']) ?>" class="nav-link <?= $currentUrl == 'site/design-view' ? 'active' : '' ?>">
                                    <i class="fa fa-circle-o nav-icon"></i>
                                    <p>Moqup preview</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= Yii::$app->urlManager->createUrl(['site/design-edit']) ?>" class="nav-link <?= $currentUrl == 'site/design-edit' ? 'active' : '' ?>">
                                    <i class="fa fa-circle-o nav-icon"></i>
                                    <p>Moqup edit</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav><!-- /.sidebar-menu -->
        </div><!-- /.sidebar -->
    </aside><!-- /.main-sidebar -->


    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <?= Alert::widget() ?>
                </div>
                <?php if (isset($this->blocks['content-header-data'])): ?>
                    <?php echo $this->blocks['content-header-data']; ?>
                <?php else: ?>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><?= Html::encode($this->title) ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <?= Breadcrumbs::widget([
                            'options'=>['class'=>'breadcrumb float-sm-right'],
                            'itemTemplate'=>'<li class="breadcrumb-item"><a href="#">{link}</a></li>',
                            'activeItemTemplate'=>'<li class="breadcrumb-item active">{link}</li>',
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                        <?= $content ?>
                </div>
            </div>
        </section><!-- /.content -->
    </div>

    <footer class="main-footer">
        <div class="container">
            <?= Html::a(Yii::t('app', 'Donate'), ['site/donate']) ?> |
            <?= Html::a(Yii::t('app', 'Career'), ['site/team']) ?> |
            <?= Html::a(Yii::t('app', 'Code repository '), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?> |
            <?= Html::a(Yii::t('app', 'Terms of Use'), ['site/terms-of-use']) ?> |
            <?= Html::a(Yii::t('app', 'Privacy Policy'), ['site/privacy-policy']) ?> |
            <?= Html::a(Yii::t('app', 'Contact'), ['site/contact']) ?> |
            <?= Html::a(Yii::t('app', 'Slack chat'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJiMzlkYmUwY2QxZTZhZGZiMzdiNmFmOGJhNDkxOTM4MDg1MDE4YmFhMWMyZWVjZjhlZmFhNjlhY2MzMDMxMTE') ?>
        </div>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
