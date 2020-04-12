<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteAsset;
use app\assets\FontAwesomeAsset;
use app\widgets\Alert;
use yii\bootstrap\Modal;
use app\widgets\Nav;
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

$this->registerCss('.main-sidebar:hover{
    width: 4.6rem !important;
}');

//List of language options
$languages = \app\models\Language::find()->orderBy(['name_ascii' => SORT_ASC])->all();
$langOpt = [];

if (!empty($languages)) {
    foreach ($languages as $lang) {
        //Check if the language is the active
        $active = ($lang->code == Yii::$app->language) ? 'active' : '';
        $langOpt[] = ['label'=>Yii::t('language', $lang->name_ascii), 'url'=>Yii::$app->urlManager->createUrl(['site/change-language', 'lang'=>$lang->code]), 'options'=>['class'=>$active]];
    }
}

$currentUrl = Yii::$app->controller->id.'/' . Yii::$app->controller->action->id;
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
<body class="sidebar-collapse">
<?php
$this->beginBody();
?>
<div class="wrapper">
    <?php
    NavBar::begin([
        'renderInnerContainer' => false,
        'options' => [
            'class' => 'main-header navbar navbar-expand bg-white navbar-light border-bottom',
        ],
    ]);

    $menuItemsLeft[] = ['label' => 'OpenSourceWebsite', 'url' => Yii::$app->homeUrl, 'options'=>['class'=>'nav-item'], 'linkOptions'=>['class'=>'nav-link']];

    $menuItemsRight[] = [
        'label' => Html::tag('span', strtoupper(Yii::$app->language)),
        'items' => $langOpt,
        'encode' => FALSE,
        'dropDownOptions' => ['id' => 'lang-menu'],
        'options' => ['class' => 'nav-item'],
        'linkOptions' => ['class' => 'nav-link'],
    ];
    $menuItemsRight[] = ['label' => 'Account', 'url' => Yii::$app->urlManager->createUrl(['site/login']), 'options'=>['class'=>'nav-item'], 'linkOptions'=>['class'=>'nav-link',]];

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
                <div class="card card-primary">
                    <div class="card-body">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </section><!-- /.content -->
    </div>

    <footer class="main-footer">
        <?= Html::a(Yii::t('app', 'Telegram Bot'), 'https://t.me/opensourcewebsite_bot') ?> |
        <?= Html::a(Yii::t('app', 'Slack'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJmMjFjOGUxNjFiZTg2OTc0ZDdkNTdhNDIzZDE2ODJiMGMzY2M5Yjg3NzEyNGMxNjIwZWE0YTFhNTE3MjhiYjY') ?> |
        <?= Html::a(Yii::t('app', 'Discord'), 'https://discord.gg/94WpSPJ') ?> |
        <?= Html::a(Yii::t('app', 'Gitter'), 'https://gitter.im/opensourcewebsite-org') ?> |
        <?= Html::a(Yii::t('app', 'Email'), 'mailto:hello@opensourcewebsite.org') ?> |
        <?= Html::a(Yii::t('app', 'GitHub'), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
