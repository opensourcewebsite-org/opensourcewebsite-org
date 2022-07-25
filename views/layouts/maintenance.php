<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteUserAsset;
use yii\helpers\Html;

AdminLteUserAsset::register($this);

$this->title = 'OpenSourceWebsite Maintenance';

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width,maximum-scale=1,initial-scale=1,user-scalable=no"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<?php $this->beginBody() ?>
<body id="home-page" class="without-main-header-menu">
<main>
    <div class="container-fluid">
        <?= $content ?>
    </div>
</main>
<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
