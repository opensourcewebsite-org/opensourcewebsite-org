<?php

use yii\helpers\Html;
use app\components\helpers\ExternalLink;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Dating');
$this->params['breadcrumbs'][] = $this->title;

?>
<p>
    Coming soon. Join to development: <?= Html::a('GitHub repository', ExternalLink::getGithubLink()); ?>.
</p>
