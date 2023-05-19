<?php

use app\assets\AdminLteAsset;
use app\assets\AdminLteUserAsset;
use app\assets\FontAwesomeAsset;
use app\components\helpers\ExternalLink;
use app\models\Language;
use app\widgets\Alert;
use app\widgets\Nav;
use app\widgets\NavBar;
use cebe\gravatar\Gravatar;
use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Breadcrumbs;

/**
 * @var View $this
 * @var string $content
 */

AdminLteAsset::register($this);
FontAwesomeAsset::register($this);
AdminLteUserAsset::register($this);

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
        'options' => ['class' => 'card-primary', 'tabindex' => false],
        'title' => Html::tag('h4', '', ['id' => 'main-modal-header', 'class' => 'modal-title']),
        'titleOptions' => ['class' => 'card-header'],
        'bodyOptions' => ['id' => 'main-modal-body'],
    ]);
Modal::end(); ?>

    <?php Modal::begin([
    'id' => 'main-modal-xl',
    'size' => Modal::SIZE_EXTRA_LARGE,
    'options' => ['class' => 'card-primary', 'tabindex' => false],
    'bodyOptions' => ['id' => 'main-modal-xl-body'],
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

echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'items' => $menuItemsLeft,
    'activateParents' => true,
]); ?>

        <div class="dropdown dropdown-inner ml-auto">
            <a class="nav-link dropdown-toggle dropbtn dropbtn-inner" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= strtoupper(Yii::$app->language) ?>
            </a>

            <div id="myDropdown" class="dropdown-menu dropdown-menu-inner" aria-labelledby="dropdownMenuLink">
                <div class="search-container">
                <input type="text" id="search-lang" placeholder="Search..">
                <button type="button"><i class="fa fa-search"></i></button>
                </div>

                <div class="dropdown-container">
                <?php
        $languages = Language::find()
            ->orderBy(['name_ascii' => SORT_ASC])
            ->all();

if (!empty($languages)) {
    /** @var Language $language */
    foreach ($languages as $language) {
        $active = ($language->code == Yii::$app->language) ? 'active' : null;
        echo Html::a(
            $language->name_ascii,
            Yii::$app->urlManager->createUrl(['site/change-language', 'lang' => $language->code]),
            ['class' => ['dropdown-item', $active]]
        );
    }
} ?>
                </div>
            </div>
        </div>

        <?php echo Nav::widget([
            'options' => ['class' => 'navbar-nav'],
            'items' => [
[
    'label' => ($userEmail = Yii::$app->user->identity->email) ? Gravatar::widget([
        'email' => $userEmail->email,
        'secure' => true,
        'options' => [
            'alt' => 'Profile Gravatar',
            'class' => 'img-circle',
        ],
        'size' => 20,
    ]) : '<i class="fas fa-user"></i>',
    'items' => [
        [
            'label' => Yii::t('app', 'Dashboard'),
            'url' => ['/dashboard'],
            'linkOptions' => [
                'tabindex' => -1,
                'class' => 'dropdown-item ' . ((Yii::$app->requestedRoute == 'user/dashboard') ? 'active' : ''),
            ]
        ],
        [
            'label' => Yii::t('app', 'Account'),
            'url' => ['/account'],
            'linkOptions' => [
                'tabindex' => -1,
                'class' => 'dropdown-item ' . ((Yii::$app->requestedRoute == 'user/account') ? 'active' : ''),
            ]
        ],
        [
            'label' => Yii::t('app', 'Merge accounts'),
            'url' => ['/merge-accounts'],
            'linkOptions' => [
                'tabindex' => -1,
                'class' => 'dropdown-item ' . ((Yii::$app->requestedRoute == 'merge-accounts/index') ? 'active' : ''),
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
    'encode' => false,
    'options' => ['class' => 'nav-item'],
    'linkOptions' => ['class' => 'nav-link'],
]
            ]
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
'title' => 'SERVICES',
'icon' => 'fas fa-cloud',
    ],
    [
'title' => 'Contacts',
'url' => 'contact',
'route' => '/contact',
    ],
    [
'title' => 'Debts',
'url' => 'debt',
'route' => '/debt',
    ],
    [
'title' => 'Support groups',
'url' => 'support-groups',
'route' => '/support-groups',
    ],
    [
'title' => Yii::t('app', 'Currency Exchange'),
'url' => 'currency-exchange-order',
'route' => '/currency-exchange-order',
    ],
    [
'title' => Yii::t('app', 'Ads'),
'urls' => [
    'ad-offer',
    'ad-search'
],
'items' => [
    [
        'title' => Yii::t('app', 'Offers'),
        'url' => 'ad-offer',
        'route' => '/ad-offer'
    ],
    [
        'title' => Yii::t('app', 'Searches'),
        'url' => 'ad-search',
        'route' => '/ad-search',
    ],
],
    ],
    [
'title' => Yii::t('app', 'Jobs'),
'urls' => [
    'company-user',
    'resume',
    'vacancy',
],
'items' => [
    [
        'title' => Yii::t('app', 'Vacancies'),
        'url' => 'vacancy',
        'route' => '/vacancy'
    ],
    [
        'title' => Yii::t('app', 'Resumes'),
        'url' => 'resume',
        'route' => '/resume'
    ],
    [
        'title' => Yii::t('app', 'Companies'),
        'url' => 'company-user',
        'route' => '/company-user'
    ],
],
    ],
    [
'title' => 'Dating',
'url' => 'dating',
'route' => '/dating',
    ],
    [
'title' => Yii::t('app', 'Telegram Bot'),
'icon' => 'fab fa-telegram',
'href' => ExternalLink::getBotLink(),
    ],
    [
'title' => 'COMMUNITY',
'icon' => 'fas fa-users',
    ],
    [
'title' => Yii::t('app', 'Settings'),
'url' => 'setting',
'route' => '/setting',
    ],
    [
'title' => Yii::t('app', 'Issues'),
'url' => 'issue',
'route' => '/issue',
    ],
    [
'title' => 'METRICS',
'icon' => 'far fa-chart-bar',
    ],
    [
'title' => Yii::t('app', 'Users'),
'url' => 'users',
'route' => '/users',
    ],
    [
'title' => Yii::t('app', 'Statistics'),
'url' => 'statistics',
'route' => '/statistics',
    ],
    [
'title' => 'SYSTEM REPORTS',
'icon' => 'far fa-list-alt',
    ],
    [
'title' => 'Cron Log',
'url' => 'cron-job',
'route' => '/cron-job/index',
    ],
    [
'title' => 'PHP Info',
'url' => 'examples/php-info',
'route' => '/examples/php-info',
    ],
    [
'title' => 'MySQL Info',
'url' => 'examples/mysql-info',
'route' => '/examples/mysql-info',
    ],
    [
'title' => Yii::t('app', 'Migrations'),
'url' => 'examples/migrations',
'route' => '/examples/migrations',
    ],
    [
'title' => 'CONTRIBUTION',
'icon' => 'fas fa-tools',
    ],
    [
'title' => Yii::t('app', 'Getting started'),
'icon' => 'fab fa-github',
'href' => ExternalLink::getGithubContributionLink(),
    ],
    [
'title' => Yii::t('app', 'Source code'),
'icon' => 'fab fa-github',
'href' => ExternalLink::getGithubLink(),
    ],
    [
'title' => 'DevOps',
'icon' => 'fab fa-github',
'href' => ExternalLink::getGithubDevopsLink(),
    ],
    [
'title' => 'Moqups',
'url' => 'moqup/design-list',
'route' => '/moqup/design-list',
    ],
    [
'title' => Yii::t('app', 'Models'),
'urls' => [
    'data/country',
    'data/currency',
    'data/language',
    'data/payment-method',
    'data/gender',
    'data/sexuality',
],
'items' => [
    [
        'title' => Yii::t('app', 'Countries'),
        'url' => 'data/country',
        'route' => '/data/country',
    ],
    [
        'title' => Yii::t('app', 'Currencies'),
        'url' => 'data/currency',
        'route' => '/data/currency',
    ],
    [
        'title' => Yii::t('app', 'Genders'),
        'url' => 'data/gender',
        'route' => 'data/gender',
    ],
    [
        'title' => Yii::t('app', 'Languages'),
        'url' => 'data/language',
        'route' => '/data/language',
    ],
    [
        'title' => Yii::t('app', 'Payment methods'),
        'url' => 'data/payment-method',
        'route' => '/data/payment-method',
    ],
    [
        'title' => Yii::t('app', 'Genders'),
        'url' => 'data/gender',
        'route' => '/data/gender',
    ],
    [
        'title' => Yii::t('app', 'Sexualities'),
        'url' => 'data/sexuality',
        'route' => '/data/sexuality',
    ],
],
    ],
    [
'title' => Yii::t('app', 'Design System'),
'urls' => [
    'examples/dashboard',
    'examples/widgets',
    'examples/charts',
    'examples/ui-elements',
    'examples/forms',
    'examples/tables',
    'examples/calendar',
    'examples/gallery',
],
'items' => [
    [
        'title' => 'Dashboard',
        'url' => 'examples/dashboard',
        'route' => '/examples/dashboard',
    ],
    [
        'title' => 'Widgets',
        'url' => 'examples/widgets',
        'route' => '/examples/widgets',
    ],
    [
        'title' => 'Charts',
        'urls' => [
            'examples/charts',
        ],
        'items' => [
            [
                'title' => 'ChartJS',
                'url' => 'examples/charts/chartjs',
                'route' => '/examples/charts/chartjs',
            ],
            [
                'title' => 'Flot',
                'url' => 'examples/charts/flot',
                'route' => '/examples/charts/flot',
            ],
            [
                'title' => 'Inline',
                'url' => 'examples/charts/inline',
                'route' => '/examples/charts/inline',
            ],
        ],
    ],
    [
        'title' => 'UI Elements',
        'urls' => [
            'examples/ui-elements',
        ],
        'items' => [
            [
                'title' => 'General',
                'url' => 'examples/ui-elements/general',
                'route' => '/examples/ui-elements/general',
            ],
            [
                'title' => 'Icons',
                'url' => 'examples/ui-elements/icons',
                'route' => '/examples/ui-elements/icons',
            ],
            [
                'title' => 'Buttons',
                'url' => 'examples/ui-elements/buttons',
                'route' => '/examples/ui-elements/buttons',
            ],
            [
                'title' => 'Sliders',
                'url' => 'examples/ui-elements/sliders',
                'route' => '/examples/ui-elements/sliders',
            ],
            [
                'title' => 'Modals & Alerts',
                'url' => 'examples/ui-elements/modals-alerts',
                'route' => '/examples/ui-elements/modals-alerts',
            ],
            [
                'title' => 'Tabs',
                'url' => 'examples/ui-elements/tabs',
                'route' => '/examples/ui-elements/tabs',
            ],
            [
                'title' => 'Timeline',
                'url' => 'examples/ui-elements/timeline',
                'route' => '/examples/ui-elements/timeline',
            ],
            [
                'title' => 'Ribbons',
                'url' => 'examples/ui-elements/ribbons',
                'route' => '/examples/ui-elements/ribbons',
            ],
        ]
    ],
    [
        'title' => 'Forms',
        'urls' => ['examples/forms'],
        'items' => [
            [
                'title' => 'General Elements',
                'url' => 'examples/forms/general-elements',
                'route' => '/examples/forms/general-elements',
            ],
            [
                'title' => 'Advenced Elements',
                'url' => 'examples/forms/advanced-elements',
                'route' => '/examples/forms/advanced-elements',
            ],
            [
                'title' => 'Editors',
                'url' => 'examples/forms/editors',
                'route' => '/examples/forms/editors',
            ],
        ]
    ],
    [
        'title' => 'Tables',
        'urls' => ['examples/tables'],
        'items' => [
            [
                'title' => 'Simple Tables',
                'url' => 'examples/tables/simple-tables',
                'route' => '/examples/tables/simple-tables',
            ],
            [
                'title' => 'DataTables',
                'url' => 'examples/tables/data-tables',
                'route' => '/examples/tables/data-tables',
            ],
            [
                'title' => 'jsGrid',
                'url' => 'examples/tables/js-grid',
                'route' => '/examples/tables/js-grid',
            ],
        ],
    ],
    [
        'title' => 'Calendar',
        'url' => 'examples/calendar',
        'route' => '/examples/calendar',
    ],
    [
        'title' => 'Gallery',
        'url' => 'examples/gallery',
        'route' => '/examples/gallery',
    ],
],
    ],
    [
'title' => 'DONATION',
'icon' => 'fas fa-donate',
    ],
    [
'title' => Yii::t('app', 'Getting started'),
'icon' => 'fab fa-github',
'href' => ExternalLink::getGithubDonationLink(),
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
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon <?= $item['icon'] ?? 'far fa-circle' ?>"></i>
                                        <p><?= $item['title'] ?><i class="fa fa-angle-left right"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <?php foreach ($item['items'] as $subItem) : ?>
                                            <?php if (isset($subItem['items'])) : ?>
                                                <li class="nav-item has-treeview  <?= in_array(Yii::$app->requestedRoute, $subItem['urls']) ? 'menu-open' : '' ?>">
                                                    <a href="#" class="nav-link">
                                                        &nbsp;&nbsp;<i class="nav-icon <?= $subItem['icon'] ?? 'far fa-circle' ?>"></i>
                                                        <p><?= $subItem['title'] ?><i class="fa fa-angle-left right"></i></p>
                                                    </a>
                                                    <ul class="nav nav-treeview <?= in_array(Yii::$app->requestedRoute, $subItem['urls']) ? 'menu-open' : '' ?>">
                                                <?php foreach ($subItem['items'] as $subItemItems) : ?>
                                                    <li class="nav-item">
                                                        <a href="<?= Yii::$app->urlManager->createUrl([$subItemItems['url']]) ?>" class="nav-link <?= (Yii::$app->request->getUrl() == $subItemItems['route']) ? 'active' : '' ?>">
                                                            &nbsp;&nbsp;&nbsp;&nbsp;<i class="nav-icon <?= $subItemItems['icon'] ?? 'far fa-circle' ?>"></i>
                                                            <p><?= $subItemItems['title'] ?></p>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                                    </ul>
                                                </li>
                                            <?php else : ?>
                                            <li class="nav-item">
                                                <a href="<?= $subItem['href'] ?? Yii::$app->urlManager->createUrl([$subItem['url']]) ?>" class="nav-link <?= (isset($subItem['route']) && (Yii::$app->request->getUrl() == $subItem['route'])) ? 'active' : '' ?>">
                                                    &nbsp;&nbsp;<i class="nav-icon <?= $subItem['icon'] ?? 'far fa-circle' ?>"></i>
                                                    <p><?= $subItem['title'] ?></p>
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php elseif (isset($item['url']) || isset($item['href'])) : ?>
                                <li class="nav-item has-treeview  <?= (isset($item['route']) && Yii::$app->request->getUrl() == $item['route']) ? 'menu-open' : '' ?>">
                                    <a href="<?= $item['href'] ?? Yii::$app->urlManager->createUrl([$item['url']]) ?>" class="nav-link <?= (isset($item['route']) && (Yii::$app->request->getUrl() == $item['route'])) ? 'active' : '' ?>">
                                        <i class="nav-icon <?= $item['icon'] ?? 'far fa-circle' ?>"></i>
                                        <p><?= $item['title'] ?></p>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-header"><i class="<?= $item['icon'] ?? 'far fa-circle' ?>"></i> <?= $item['title'] ?></li>
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
    });

    $(document).on('click', '.modal-btn-ajax', function(e){
        e.preventDefault();
        $('#main-modal')
            .find('.modal-content')
            .empty()
            .load($(this).attr('href'), function(){
                $('#main-modal').modal('show')
            });

        return false;
    });
</script>
</body>
</html>
<?php $this->endPage() ?>
