<?php
/**
 * @var $job \app\modules\apiTesting\models\ApiTestJob
 */
use app\components\helpers\ArrayHelper;
use app\modules\apiTesting\models\ApiTestRequest;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

foreach ($job->project->getLabels()->orderBy('name')->all() as $label) {
    $data[$label->name] = ArrayHelper:: map($label->getRequests()->orderBy('name')->all(), 'id', 'name');
}

$data['Without label'] = ArrayHelper::map(ApiTestRequest::find()->andWhere(['server_id' => $job->project->getServers()->select('id')->column()])
    ->joinWith(['responses', 'labels l'])
    ->andWhere(['IS', 'l.id', null])
    ->orderBy('name')->all(), 'id', 'name');
?>
<?php $form = ActiveForm::begin(); ?>
    <?=$form->field($job, 'requestIds')->widget(Select2::className(), [
        'data' => $data,
        'options' => [
            'multiple' => true
        ]
    ]); ?>
    <?= CancelButton::widget(); ?>
    <?= SaveButton::widget(); ?>

<?php ActiveForm::end(); ?>
