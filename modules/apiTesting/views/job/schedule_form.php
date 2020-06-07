<?php

use kartik\date\DatePicker;
use yii\widgets\ActiveForm;

/**
 * @var $schedule \app\modules\apiTesting\models\ApiTestJobSchedule
 * @var $this \yii\web\View
 */
$this->title = "Configure schedule";
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => '/apiTesting/project'];
$this->params['breadcrumbs'][] = ['label' => $project->name.' testing', 'url' => '/apiTesting/project/testing', 'id' => $project->id];
$this->params['breadcrumbs'][] = ['label' => $job->name.' job', 'url' => ['/apiTesting/job/view', 'id' => $job->id]];
$this->params['breadcrumbs'][] = $this->title;
$presetedStatus = $schedule->isNewRecord ? ['value' => 1] : [];
?>
<?php if ($schedule->isNewRecord || $schedule->schedule_periodicity != $schedule::PERIODICITY_CUSTOM): ?>
    <?php $this->registerJs('
        $("#apitestjobschedule-custom_schedule_from_date-kvdate").hide();
    '); ?>
<?php endif; ?>
<div class="card">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>

        <?=$form->field($schedule, 'schedule_periodicity')->radioList($schedule::getPeriodicityList(), ['id' => 'schedule-period']); ?>
        <?= DatePicker::widget([
            'model' => $schedule,
            'type' => DatePicker::TYPE_RANGE,
            'attribute' => 'custom_schedule_from_date',
            'attribute2' => 'custom_schedule_end_date',

        ]); ?>
        <br>
        <?=$form->field($schedule, 'status')->checkbox($presetedStatus); ?>
        <?=$form->field($schedule, 'description')->textarea(); ?>
        <?=\app\widgets\buttons\SaveButton::widget(); ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
$this->registerJs("
    $('input[type=radio]').change(function() {
           
           if($(this).val() == 4) {
                $('#apitestjobschedule-custom_schedule_from_date-kvdate').show();
           } else {
                $('#apitestjobschedule-custom_schedule_from_date-kvdate').hide();
            }
    })
");
?>

