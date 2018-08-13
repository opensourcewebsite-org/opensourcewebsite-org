<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use cebe\gravatar\Gravatar;

AppAsset::register($this);

$this->registerCss('#lang-menu {
    overflow: auto;
    max-height: 200px;
}');

//List of language options
$languages = \app\models\Language::find()->all();
$langOpt = '';

if (!empty($languages)) {
    foreach ($languages as $lang) {
        //Check if the language is the active
        $active = ($lang->code == Yii::$app->language) ? 'active' : '';
        $langOpt .= '<li class="'.$active.'">'.Html::a(Yii::t('language', $lang->name_ascii), ['site/change-language', 'lang' => $lang->code]).'</li>';
    }
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?php if (Yii::$app->user->isGuest && file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'analytics.php')) {
        echo $this->render('analytics');
    } ?>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name . ($this->title ? " - $this->title" : '')) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '
            <ul class="navbar-nav navbar-right nav">
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown"> ' . Gravatar::widget([
                        'email' => Yii::$app->user->identity->email,
                        'options' => [
                            'alt' => 'Profile Gravatar',
                            'class' => 'img-circle',
                        ],
                        'size' => 30
                    ]) . ' ' . Yii::$app->user->identity->email . ' <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li>' . Html::a(Yii::t('app', 'Account'), ['site/account'], ['tabindex' => -1]) . '</li>
                        <li>' . Html::a(Yii::t('app', 'Logout'), ['site/logout'], ['data-method' => 'post', 'tabindex' => -1]) . '</li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-globe"></span>
                        '.(Yii::t('menu', 'Language')).'
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" id="lang-menu">
                        '.$langOpt.'
                    </ul>
                </li>
            </ul>
        ';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <?= Html::a(Yii::t('app', 'Donate'), ['site/donate']) ?> |
        <?= Html::a(Yii::t('app', 'Career'), ['site/team']) ?> |
        <?= Html::a(Yii::t('app', 'Code repository '), 'https://gitlab.com/opensourcewebsite-org/opensourcewebsite-org') ?> |
        <?= Html::a(Yii::t('app', 'Issues'), 'https://gitlab.com/opensourcewebsite-org/opensourcewebsite-org/issues') ?> |
        <?= Html::a(Yii::t('app', 'Wiki'), 'https://gitlab.com/opensourcewebsite-org/opensourcewebsite-org/wikis/home') ?> |
        <?= Html::a(Yii::t('app', 'Terms of Use'), ['site/terms-of-use']) ?> |
        <?= Html::a(Yii::t('app', 'Privacy Policy'), ['site/privacy-policy']) ?> |
        <?= Html::a(Yii::t('app', 'Contact'), ['site/contact']) ?> |
        <?= Html::a(Yii::t('app', 'Slack chat'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJiMzlkYmUwY2QxZTZhZGZiMzdiNmFmOGJhNDkxOTM4MDg1MDE4YmFhMWMyZWVjZjhlZmFhNjlhY2MzMDMxMTE') ?>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
