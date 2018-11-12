<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\widgets\Alert;
use yii\bootstrap\Modal;
use app\assets\AppAsset;
use yii\widgets\Breadcrumbs;
use app\assets\AdminLteAsset;
use app\assets\FontAwesomeAsset;

AppAsset::register($this);
AdminLteAsset::register($this);
FontAwesomeAsset::register($this);

$currentUrl = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

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
    <body class="sidebar-mini">
        <?php $this->beginBody() ?>
        <?php Modal::begin([
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
            'options' => ['class' => 'card-primary', 'tabindex' => false],
            'header' => Html::tag('h4', '', ['id' => 'main-modal-header', 'class' => 'modal-title']),
            'headerOptions' => ['class' => 'card-header'],
            'bodyOptions' => ['id' => 'main-modal-body'],
        ]);
        Modal::end(); ?>
        <div class="wrapper">
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <!-- Brand Logo -->
                <a href="<?= Yii::$app->homeUrl ?>" class="brand-link">
                    <span class="brand-abbr font-weight-light">OSW</span>
                    <span class="brand-text font-weight-light">OSW</span>
                </a>

                <div class="sidebar">
                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item has-treeview  <?= in_array($currentUrl, ['moqup/design-list', 'moqup/design-add', 'moqup/design-view', 'moqup/design-edit', 'user/display']) ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= in_array($currentUrl, ['moqup/design-list', 'moqup/design-add', 'moqup/design-view', 'moqup/design-edit', 'user/display']) ? 'active' : '' ?>">
                                    <i class="nav-icon fa fa-edit"></i>
                                    <p>Developer<i class="fa fa-angle-left right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['moqup/design-list']) ?>" class="nav-link <?= in_array($currentUrl, ['moqup/design-list', 'moqup/design-add', 'moqup/design-view', 'moqup/design-edit']) ? 'active' : '' ?>">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Moqups</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['user/display']) ?>" class="nav-link <?= in_array($currentUrl, ['user/display']) ? 'active' : '' ?>">
                                            <i class="fa fa-users nav-icon"></i>
                                            <p>Users</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item has-treeview  <?= in_array($currentUrl, ['data/country', 'data/currency', 'data/language', 'data/setting']) ? 'menu-open' : '' ?>">
                                <a href="#" class="nav-link <?= in_array($currentUrl, ['data/country', 'data/currency', 'data/language', 'data/setting']) ? 'active' : '' ?>">
                                    <i class="nav-icon fa fa-edit"></i>
                                    <p>Data<i class="fa fa-angle-left right"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['data/country']) ?>" class="nav-link <?= in_array($currentUrl, ['data/country']) ? 'active' : '' ?>">
                                            <i class="fa fa-map-signs nav-icon"></i>
                                            <p>Country</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['data/currency']) ?>" class="nav-link <?= in_array($currentUrl, ['data/currency']) ? 'active' : '' ?>">
                                            <i class="fa fa-credit-card nav-icon"></i>
                                            <p>Currency</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['data/language']) ?>" class="nav-link <?= in_array($currentUrl, ['data/language']) ? 'active' : '' ?>">
                                            <i class="fa fa-language nav-icon"></i>
                                            <p>Language</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="<?= Yii::$app->urlManager->createUrl(['data/setting']) ?>" class="nav-link <?= in_array($currentUrl, ['data/setting']) ? 'active' : '' ?>">
                                            <i class="fa fa-microchip nav-icon"></i>
                                            <p>Setting</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item has-treeview  <?= in_array($currentUrl, ['wikipedia-pages/index']) ? 'menu-open' : '' ?>">
                                <a href="<?= Yii::$app->urlManager->createUrl(['wikipedia-pages']) ?>" class="nav-link <?= in_array($currentUrl, ['wikipedia-pages/index']) ? 'active' : '' ?>">
                                    <i class="nav-icon fa fa-book"></i>
                                    <p>Wikipedia Watchlists</p>
                                </a>
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
                                        'options' => ['class' => 'breadcrumb float-sm-right'],
                                        'itemTemplate' => '<li class="breadcrumb-item"><a href="#">{link}</a></li>',
                                        'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
                                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                    ]); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><!-- /.content-header -->

                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <?= $content ?>
                    </div>
                </section><!-- /.content -->
            </div>

            <footer class="main-footer">
                <?= Html::a(Yii::t('app', 'Donate'), ['site/donate']) ?> |
                <?= Html::a(Yii::t('app', 'Contribution'), ['site/team']) ?> |
                <?= Html::a(Yii::t('app', 'We\'re on GitHub'), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?> |
                <?= Html::a(Yii::t('app', 'Join us on Slack'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJiMzlkYmUwY2QxZTZhZGZiMzdiNmFmOGJhNDkxOTM4MDg1MDE4YmFhMWMyZWVjZjhlZmFhNjlhY2MzMDMxMTE') ?> |
                <?= Html::a(Yii::t('app', 'Contact us'), ['site/contact']) ?><br />
                <?= Html::a(Yii::t('app', 'Terms of Use'), ['site/terms-of-use']) ?> |
                <?= Html::a(Yii::t('app', 'Privacy Policy'), ['site/privacy-policy']) ?>
            </footer>
        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>