<?php

use app\components\helpers\Icon;
use app\modules\apiTesting\models\ApiTestJob as ApiTestJob;
use app\widgets\buttons\AddButton;
use app\widgets\ModalAjax;
use yii\grid\GridView;
use yii\helpers\Url;

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var ApiTestJob $job
 */
?>

<?=\app\widgets\buttons\AddButton::widget([
    'url' => Url::to(['create-schedule', 'id' => $job->id]),
    'options' => [
        'style' => [
            'position' => 'absolute',
            'right' => '20px',
            'top' => '10px'
        ]
    ]
]);
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'tableOptions' => ['class' => 'table table-hover'],
    'columns' => [
        'description',
        [
            'header' => 'Next run',
            'value' => function (\app\modules\apiTesting\models\ApiTestJobSchedule $schedule) {
                $lastRun = $schedule->getRunners()->orderBy('start_at DESC')->one();
                if ( ! $lastRun) {
                    return 'Today';
                }

                switch ($schedule->schedule_periodicity):
                        case  $schedule::PERIODICITY_CUSTOM:
                            if ($schedule->custom_schedule_end_date > time()) {
                                Yii::$app->formatter->asRelativeTime(strtotime('+1 day', $lastRun->start_at), $lastRun->start_at);
                            } else {
                                return 'Never';
                            }
                break;
                case $schedule::PERIODICITY_EVERYDAY:
                            return Yii::$app->formatter->asRelativeTime(strtotime('+1 day', $lastRun->start_at), $lastRun->start_at);
                break;
                case $schedule::PERIODICITY_EVERY_MONTH:
                            return Yii::$app->formatter->asRelativeTime(strtotime('+1 month', $lastRun->start_at), $lastRun->start_at);
                break;
                case $schedule::PERIODICITY_EVERY_WEEK:
                            return Yii::$app->formatter->asRelativeTime(strtotime('+7 day', $lastRun->start_at), $lastRun->start_at);
                break;
                endswitch;
            },
        ],
        [
            'format' => 'raw',
            'value' => function (\app\modules\apiTesting\models\ApiTestJobSchedule $schedule) {
                return  \app\widgets\buttons\EditButton::widget([
                    'url' => ['update-schedule', 'id' => $schedule->id],
                ]).\app\widgets\buttons\DeleteButton::widget([
                    'url' => ['delete-schedule', 'id' => $schedule->id],
                ]);
            }
        ]
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
