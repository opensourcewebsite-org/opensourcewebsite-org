<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AgencyAsset;
use app\assets\FontAwesomeAsset;
use yii\helpers\Html;
use app\models\Language;

AgencyAsset::register($this);
FontAwesomeAsset::register($this);

$currentUrl = Yii::$app->controller->id.'/' . Yii::$app->controller->action->id;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?php if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'analytics.php')) {
    echo $this->render('analytics');
} ?>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name . ($this->title ? " - $this->title" : '')) ?></title>
    <?php $this->head() ?>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Kaushan+Script' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700' rel='stylesheet' type='text/css'>
</head>
<body id="page-top">
<?php
$this->beginBody();
?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
  <div class="container header-container">
    <a class="navbar-brand js-scroll-trigger" href="<?= Yii::$app->homeUrl ?>">OpenSourceWebsite (OSW)</a>
    <div class="">
      <ul class="navbar-nav text-uppercase ml-auto">
        <li class="nav-item">
          <div class="dropdown">
            <a class="nav-link dropbtn" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <?= strtoupper(Yii::$app->language) ?> <i class="fas fa-angle-down drop-btn-icon"></i>
            </a>

            <div id="myDropdown" class="dropdown-menu" aria-labelledby="dropdownMenuLink">
              <div class="search-container">
                <input type="text" id="search-lang" onkeyup="getLanguage()" placeholder="Search..">
                <button type="button"><i class="fa fa-search"></i></button>
              </div>
                <div class="dropdown-container">
                <?php
                //List of language options
                $languages = Language::find()->orderBy(['name_ascii' => SORT_ASC])->all();

                if (!empty($languages)) {
                    foreach ($languages as $language) {
                        //Check if the language is the active
                        $active = ($language->code == Yii::$app->language) ? 'active' : null;
                        echo Html::a($language->name_ascii, Yii::$app->urlManager->createUrl(['site/change-language', 'lang' => $language->code]), ['class' => ['dropdown-item', $active]]);
                    }
                } ?>
                </div>
            </div>
          </div>
        </li>

        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>"><?= Yii::t('app', 'Account') ?></a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<?= $content ?>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-12">
            <ul class="list-inline quicklinks">
              <li class="list-inline-item">
                <?= Html::a(Yii::t('app', 'Telegram Bot'), 'https://t.me/opensourcewebsite_bot') ?>
              </li>
              <li class="list-inline-item">
                <?= Html::a(Yii::t('app', 'Source Code'), 'https://github.com/opensourcewebsite-org/opensourcewebsite-org') ?>
              </li>
              <li class="list-inline-item">
                <?= Html::a(Yii::t('app', 'Terms of Use'), ['terms-of-use'], ['target' => '_blank']) ?>
              </li>
              <li class="list-inline-item">
                <?= Html::a(Yii::t('app', 'Privacy Policy'), ['privacy-policy'], ['target' => '_blank']) ?>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>

<?php $this->endBody() ?>
<script>
    function getLanguage() {
        let input = document.getElementById("search-lang");
        let filter = input.value.toLowerCase();
        let nodes = document.getElementsByClassName('dropdown-item');
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].innerText.toLowerCase().includes(filter)) {
                nodes[i].style.display = "block";
            } else {
                nodes[i].style.display = "none";
            }
        }
    }
</script>
</body>
</html>
<?php $this->endPage() ?>
