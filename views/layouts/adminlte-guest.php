<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteGuestAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use cebe\gravatar\Gravatar;

AdminLteGuestAsset::register($this);

$this->registerCssFile('@web/css/adminlte-fix.css', [
    'depends' => [\yii\bootstrap\BootstrapAsset::className()],
]);
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
<body class="sidebar-mini sidebar-collapse">
<?php $this->beginBody() ?>
<div class="wrapper">
    <?php
    NavBar::begin([
        'options' => [
            'class' => 'main-header navbar navbar-expand bg-white navbar-light border-bottom',
        ],
    ]);

    $menuItemsLeft[] = ['label' => 'OpenSourceWebsite', 'url' => Yii::$app->homeUrl, 'options'=>['class'=>'nav-item'], 'linkOptions'=>['class'=>'nav-link']];
    $menuItemsRight[] = ['label' => 'Signup', 'url' => ['/site/signup'], 'options'=>['class'=>'nav-item'], 'linkOptions'=>['class'=>'nav-link']];
    $menuItemsRight[] = ['label' => 'Login', 'url' => ['/site/login'], 'options'=>['class'=>'nav-item'], 'linkOptions'=>['class'=>'nav-link']];

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
        <div class="container">
            <?= Html::a(Yii::t('app', 'Donate'), ['site/donate']) ?> |
            <?= Html::a(Yii::t('app', 'Career'), ['site/team']) ?> |
            <?= Html::a(Yii::t('app', 'Code repository '), 'https://gitlab.com/opensourcewebsite-org/opensourcewebsite-org') ?> |
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
