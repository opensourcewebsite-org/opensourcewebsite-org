<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteAsset;
use app\assets\FontAwesomeAsset;
use app\assets\AdminLteUserAsset;
use app\widgets\Alert;
use app\widgets\Nav;
use app\widgets\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use cebe\gravatar\Gravatar;
use yii\bootstrap4\Modal;

AdminLteAsset::register($this);
FontAwesomeAsset::register($this);
AdminLteUserAsset::register($this);

$this->registerCss('#lang-menu{
    overflow: auto;
    min-width: 300px;
    max-height: 200px;
}#search-lang{
    display: block;
    width: 100%;
    padding: .25rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    border-bottom: 1px solid #eee;');

//List of language options
$languages = \app\models\Language::find()->orderBy(['name_ascii' => SORT_ASC])->all();
$langOpt = [];

if (!empty($languages)) {
    foreach ($languages as $lang) {
        //Check if the language is the active
        $active = ($lang->code == Yii::$app->language) ? 'active' : '';
        $langOpt[] = ['label' => Yii::t('language', $lang->name_ascii), 'url'=>Yii::$app->urlManager->createUrl(['site/change-language', 'lang'=>$lang->code]), 'linkOptions' => ['class' => "dropdown-item $active"]];
    }
}
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
        'title' => Html::tag('h4', '', ['id' => 'main-modal-header', 'class' => 'modal-title']),
        'titleOptions' => ['class' => 'card-header'],
        'bodyOptions' => ['id' => 'main-modal-body'],
    ]);
    Modal::end(); ?>
    <div class="wrapper">
        <?php
        NavBar::begin([
            'renderInnerContainer' => false,
            'options' => [
                    'class' => 'main-header navbar navbar-expand bg-white navbar-light border-bottom',
            ],
        ]);

        $menuItemsLeft[] = ['label' => '<i class="fa fa-bars"></i>', 'url' => '#', 'options' => ['class' => 'nav-item', 'data-widget' => 'pushmenu'], 'linkOptions' => ['class' => 'nav-link'], 'encode' => false];

        $menuItemsRight[] = [
            'label' => Html::tag('span', strtoupper(Yii::$app->language)),
            'items' => $langOpt,
            'encode' => FALSE,
            'dropDownOptions' => ['id' => 'lang-menu'],
            'options' => ['class' => 'nav-item'],
            'linkOptions' => ['class' => 'nav-link'],
        ];
        $menuItemsRight[] = [
            'label' => Gravatar::widget([
                'email' => Yii::$app->user->identity->email,
                'options' => [
                    'alt' => 'Profile Gravatar',
                    'class' => 'img-circle',
                ],
                'size' => 20
            ]),
            'items' => [
                [
                    'label' => Yii::t('app', 'Account'),
                    'url' => ['site/account'],
                    'linkOptions' => [
                        'tabindex' => -1,
                        'class' => 'dropdown-item ' . ((Yii::$app->requestedRoute == 'site/account') ? 'active' : ''),
                    ]
                ],
                [
                    'label' => Yii::t('app', 'Loyalty program'),
                    'url' => ['/referrals'],
                    'linkOptions' => [
                        'tabindex' => -1,
                        'class' => 'dropdown-item ' . ((Yii::$app->requestedRoute == 'referrals/index') ? 'active' : ''),
                    ]
                ],
                [
                    'label' => Yii::t('app', 'Logout'),
                    'url' => ['site/logout'],
                    'linkOptions' => [
                        'data-method' => 'post',
                        'tabindex' => -1,
                        'class' => 'dropdown-item'
                    ]
                ],
            ],
            'encode' => FALSE,
            'options' => ['class' => 'nav-item'],
            'linkOptions' => ['class' => 'nav-link'],
        ];

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav'],
            'items' => $menuItemsLeft,
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav ml-auto'],
            'items' => $menuItemsRight,
        ]);
        NavBar::end();
        ?>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="<?= Yii::$app->homeUrl ?>" class="brand-link">
                <span class="brand-abbr font-weight-light">OSW</span>
            </a>

<?php
$leftMenuItems = [
    [
        'title' => 'Data',
        'icon' => 'fas fa-database',
        'urls' => ['data/country', 'data/currency', 'data/language', 'data/payment-method', 'data/gender', 'data/sexuality'],
        'items' => [
            [
                'title' => 'Countries',
                'icon' => 'far fa-circle',
                'url' => 'data/country',
                'route' => 'data/country',
            ],
            [
                'title' => 'Currencies',
                'icon' => 'far fa-circle',
                'url' => 'data/currency',
                'route' => 'data/currency',
            ],
            [
                'title' => 'Languages',
                'icon' => 'far fa-circle',
                'url' => 'data/language',
                'route' => 'data/language',
            ],
            [
                'title' => 'Payment methods',
                'icon' => 'far fa-circle',
                'url' => 'data/payment-method',
                'route' => 'data/payment-method',
            ],
            [
                'title' => 'Genders',
                'icon' => 'far fa-circle',
                'url' => 'data/gender',
                'route' => 'data/gender',
            ],
            [
                'title' => 'Sexualities',
                'icon' => 'far fa-circle',
                'url' => 'data/sexuality',
                'route' => 'data/sexuality',
            ],
        ],
    ],
    [
        'title' => 'Issues',
        'icon' => 'fa fa-edit',
        'url' => 'issue',
        'route' => 'issue',
    ],
    [
        'title' => 'Website settings',
        'icon' => 'fa fa-microchip',
        'url' => 'website-settings',
        'route' => 'setting/index',
    ],
    [
        'title' => 'Moqups',
        'icon' => 'fa fa-edit',
        'url' => 'moqup/design-list',
        'route' => 'moqup/design-list',
    ],
    [
        'title' => 'Users',
        'icon' => 'fa fa-users',
        'url' => 'user/display',
        'route' => 'user/display',
    ],
    [
        'title' => 'Wikipedia Watchlists',
        'icon' => 'fa fa-book',
        'url' => 'wikipedia-pages',
        'route' => 'wikipedia-pages/index',
    ],
    [
        'title' => 'Wikinews pages',
        'icon' => 'fa fa-book',
        'url' => 'wikinews-pages',
        'route' => 'wikinews-pages/index',
    ],
    [
        'title' => 'Cron Job Log',
        'icon' => 'far fa-list-alt',
        'url' => 'cron-job',
        'route' => 'cron-job/index',
    ],
    [
        'title' => 'Support groups',
        'icon' => 'fa fa-users',
        'url' => 'support-groups',
        'route' => 'support-groups',
    ],
    [
        'title' => 'Contacts',
        'icon' => 'fa fa-address-card',
        'url' => 'contact',
        'route' => 'contact',
    ],
    [
        'title' => 'Debts',
        'icon' => 'fa fa-credit-card',
        'url' => 'debt',
        'route' => 'debt',
    ],
];
?>
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <?php foreach ($leftMenuItems as $item) : ?>
                            <?php if (isset($item['items'])) : ?>
                                <li class="nav-item has-treeview  <?= in_array(Yii::$app->requestedRoute, $item['urls']) ? 'menu-open' : '' ?>">
                                    <a href="#" class="nav-link <?= in_array(Yii::$app->requestedRoute, $item['urls']) ? 'active' : '' ?>">
                                        <i class="nav-icon <?= $item['icon'] ?>"></i>
                                        <p><?= $item['title'] ?><i class="fa fa-angle-left right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <?php foreach ($item['items'] as $subItem) : ?>
                                            <li class="nav-item">
                                                <a href="<?= Yii::$app->urlManager->createUrl([$subItem['url']]) ?>" class="nav-link <?= (Yii::$app->requestedRoute == $subItem['route']) ? 'active' : '' ?>">
                                                    <i class="nav-icon <?= $subItem['icon'] ?>"></i>
                                                    <p><?= $subItem['title'] ?></p>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else : ?>
                                <li class="nav-item has-treeview  <?= (Yii::$app->requestedRoute == $item['route']) ? 'menu-open' : '' ?>">
                                    <a href="<?= Yii::$app->urlManager->createUrl([$item['url']]) ?>" class="nav-link <?= (Yii::$app->requestedRoute == $item['route']) ? 'active' : '' ?>">
                                        <i class="nav-icon <?= $item['icon'] ?>"></i>
                                        <p><?= $item['title'] ?></p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
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
                                <?=
                                Breadcrumbs::widget([
                                    'options' => ['class' => 'breadcrumb float-sm-right'],
                                    'itemTemplate' => '<li class="breadcrumb-item"><a href="#">{link}</a></li>',
                                    'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
                                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                ])
                                ?>
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
            <?= Html::a(Yii::t('app', 'Telegram Bot'), 'https://t.me/opensourcewebsite_bot') ?> |
            <?= Html::a(Yii::t('app', 'Slack'), 'https://join.slack.com/t/opensourcewebsite/shared_invite/enQtNDE0MDc2OTcxMDExLWJmMjFjOGUxNjFiZTg2OTc0ZDdkNTdhNDIzZDE2ODJiMGMzY2M5Yjg3NzEyNGMxNjIwZWE0YTFhNTE3MjhiYjY') ?> |
            <?= Html::a(Yii::t('app', 'Discord'), 'https://discord.gg/94WpSPJ') ?> |
            <?= Html::a(Yii::t('app', 'Gitter'), 'https://gitter.im/opensourcewebsite-org') ?> |
            <?= Html::a(Yii::t('app', 'Email'), 'mailto:hello@opensourcewebsite.org') ?> |
            <?= Html::a(Yii::t('app', 'GitHub'), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?>
        </footer>
    </div>
<?php $this->endBody() ?>
<script>
    $(document).ready(function () {
        $('#lang-menu').prepend('<div><input type="text" id="search-lang" placeholder="Search.."></div>');
        $('#search-lang')
            .keyup(function() {
                var input = $(this).val();
                var filter = input.toLowerCase();
                var nodes = $('.dropdown-item');
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].innerText.toLowerCase().includes(filter)) {
                        nodes[i].style.display = "block";
                    } else {
                        nodes[i].style.display = "none";
                    }
                }
            })
            .keyup();
    })
</script>
</body>
</html>
<?php $this->endPage() ?>
