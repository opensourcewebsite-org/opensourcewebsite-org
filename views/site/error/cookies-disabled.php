<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */

/* @var $exception Exception */

use yii\helpers\Html;
use app\components\helpers\ExternalLink;

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title); ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)); ?>.<br>
        <?= Yii::t('app', 'Your browser doesn`t support cookies'); ?>. <?= Yii::t('app', 'Please, enable it in browser preferences for using all capabilities of Open Source Website'); ?>.
    </div>

    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please <?= Html::a('contact', ExternalLink::getGithubIssuesLink()); ?> us if you think this is a server error. Thank you.
    </p>

</div>
