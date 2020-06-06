<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 */
use app\components\helpers\ArrayHelper;
use yii\widgets\ActiveForm as ActiveForm;

?>
<?php $form = ActiveForm::begin(); ?>
    <?=$form->field($model, 'labelIds')->widget(\kartik\select2\Select2::className(), [
        'data' => ArrayHelper::map($model->server->project->labels, 'id', 'name'),
        'options' => [
            'multiple' => true
        ]
    ]); ?>
    <?= \app\widgets\buttons\CancelButton::widget([

    ]); ?>
    <?= \app\widgets\buttons\SaveButton::widget(); ?>
<?php ActiveForm::end(); ?>
