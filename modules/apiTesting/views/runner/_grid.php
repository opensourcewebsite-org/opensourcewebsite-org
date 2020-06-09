<?php

use app\modules\apiTesting\models\ApiTestRunner;
use yii\grid\GridView;
use yii\helpers\Html;

?>
<div class="card">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => false,
        'tableOptions' => ['class' => 'table table-hover'],
        'columns' => [
            [
                'header' => 'Triggered by',
                'format' => 'raw',
                'value' => function (ApiTestRunner $model) {
                    $triggeredBy = 'triggered by ';

                    if ($model->user) {
                        $triggeredBy .= $model->user->name == '' ? '#'.$model->user->id : $model->user->name;
                    }

                    if ($model->schedule) {
                        $triggeredBy .= $model->schedule->description;
                    }

                    if ($model->isJob()) {
                        return $model->job->name.' job '.$triggeredBy;
                    } elseif ($model->isRequest()) {
                        return $model->request->name.' request '.$triggeredBy;
                    }
                },
            ],
            [
                'header' => 'Job status',
                'format' => 'raw',
                'value' => function (ApiTestRunner $model) {
                    return Html::tag('span', $model->getStatusLabel(), [
                        'class' => 'badge badge-'.$model->getStatusColorClass()
                    ]);
                }
            ],
            [
                'header' => 'Latest test status',
                'format' => 'raw',
                'value' => function (ApiTestRunner $model) {
                    if ( ! empty($model->request->latestResponse) && $model->status != $model::STATUS_WAITING && $model->status != $model::STATUS_IN_PROGRESS) {
                        return $this->render('../common/_test_result_column', [
                            'response' => $model->request->latestResponse
                        ]);
                    }
                }
            ],
            'timing:relativeTime'

        ],
        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
        'pager' => [
            'options' => [
                'class' => 'pagination float-right',
            ],
            'linkContainerOptions' => [
                'class' => 'page-item',
            ],
            'linkOptions' => [
                'class' => 'page-link',
            ],
            'maxButtonCount' => 5,
            'disabledListItemSubTagOptions' => [
                'tag' => 'a',
                'class' => 'page-link',
            ],
        ],
    ]); ?>

</div>
