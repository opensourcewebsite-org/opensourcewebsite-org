<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\assets\AdminLteAsset;
use app\assets\FontAwesomeAsset;
use app\assets\AdminLteUserAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use app\widgets\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use cebe\gravatar\Gravatar;

AdminLteAsset::register($this);
FontAwesomeAsset::register($this);
AdminLteUserAsset::register($this);

$this->registerCss('.content-wrapper{
    margin-left: 0;
}');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" style="font-size: 14px">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Yii::t('moqup', 'Moqup Preview') ?></title>
        <?php $this->head() ?>
        <style id="prev-style"></style>
    </head>
    <body>
        <?php $this->beginBody() ?>
        <div class="wrapper">
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid"></div>
                </div><!-- /.content-header -->

                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <?= $content ?>
                    </div>
                </section><!-- /.content -->
            </div>
        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
