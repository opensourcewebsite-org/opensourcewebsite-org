<?php

use yii\helpers\Html;
use app\models\CronJob;
use yii\grid\GridView;

/* @var $this \yii\web\View */
/* @var $searchModel app\models\search\CronJobSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $jobs \app\models\CronJob[] */
/* @var $jobId int|null */

$this->title = Yii::t('menu', 'Cron Job Log');

?>

<div class="card">
    <div class="card-header d-flex p-0">
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <?= CronJob::renderMenu($jobs, $jobId) ?>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary'      => false,
            'tableOptions' => ['class' => 'table table-hover'],
            'pager'        => [
                'options' => [
                    'class' => 'pagination ml-4',
                ],
                'linkContainerOptions' => [
                    'class' => 'page-item',
                ],
                'linkOptions' => [
                    'class' => 'page-link',
                ],
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a', 'class' => 'page-link',
                ],
            ],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'format'    => [
                        'datetime', 'php:d.m.Y H:i:s',
                    ],
                ],
                'message',
                [
                    'attribute' => 'cronJob.name',
                    'visible'   => ($jobId) ? false : true,
                ],
            ],
        ]) ?>
    </div>

</div>
