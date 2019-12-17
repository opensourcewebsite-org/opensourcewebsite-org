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
    <?php if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'analytics.php')) {
        echo $this->render('analytics');
    } ?>
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
Modal::begin([
    'id' => 'main-modal',
    'size' => Modal::SIZE_LARGE,
    'closeButton' => false,
    'clientEvents' => [
        'show.bs.modal' => 'function (e) {
            $("#main-modal").addClass("show");
        }',
        'hide.bs.modal' => 'function (e) {
            $("#main-modal").removeClass("show");
        }',
    ],
    'footer' => Html::button(Yii::t('app', 'Close'), [
        'class' => 'btn btn-default',
        'data-dismiss' => 'modal',
    ])
    . Html::button(Yii::t('app', 'Submit'), [
        'class' => 'btn btn-primary',
        'onclick' => 'var data = $("#main-modal-body").find("form").serialize();'
            . 'var target = $("#main-modal-header").data("target");'
            . '$.post(target, {"data":data}, function (result){
                $("#main-modal-body").html(result);
            })'
    ]),
    'options' => ['class' => 'card-primary'],
    'header' => Html::tag('h4', '', ['id' => 'main-modal-header', 'class' => 'modal-title']),
    'headerOptions' => ['class' => 'card-header'],
    'bodyOptions' => ['id' => 'main-modal-body'],
]);
Modal::end();
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
    $menuItemsRight[] = ['label' => 'Signup', 'url' => '#', 'options'=>['class'=>'nav-item'], 'linkOptions'=>[
        'class'=>'nav-link',
        'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['site/signup']) .'", {}, function (result){
            $("#main-modal-body").html(result);
            $("#main-modal-header").html("' . Yii::t('app', 'Signup') . '").data("target", "' . Yii::$app->urlManager->createUrl(['site/signup']) . '");
            $("#main-modal").modal("show");
        })',
    ]];
    $menuItemsRight[] = ['label' => 'Login', 'url' => '#', 'options'=>['class'=>'nav-item'], 'linkOptions'=>[
        'class'=>'nav-link',
        'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['site/login']) .'", {}, function (result){
            $("#main-modal-body").html(result);
            $("#main-modal-header").html("' . Yii::t('app', 'Login') . '").data("target", "' . Yii::$app->urlManager->createUrl(['site/login']) . '");
            $("#main-modal").modal("show");
        })',
    ]];

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
            <?= Html::a(Yii::t('app', 'Road Map'), ['site/road-map']) ?> |
            <?= Html::a(Yii::t('app', 'Telegram Bot'), 'https://t.me/opensourcewebsite_bot') ?> |
            <?= Html::a(Yii::t('app', 'Gitter'), 'https://gitter.im/opensourcewebsite-org') ?> |
            <?= Html::a(Yii::t('app', 'Slack'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJmMjFjOGUxNjFiZTg2OTc0ZDdkNTdhNDIzZDE2ODJiMGMzY2M5Yjg3NzEyNGMxNjIwZWE0YTFhNTE3MjhiYjY') ?> |
            <?= Html::a(Yii::t('app', 'Email'), 'mailto:hello@opensourcewebsite.org') ?> |
            <?= Html::a(Yii::t('app', 'GitHub'), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?> |
            <?= Html::a(Yii::t('app', 'Terms of Use'), ['site/terms-of-use']) ?> |
            <?= Html::a(Yii::t('app', 'Privacy Policy'), ['site/privacy-policy']) ?>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
