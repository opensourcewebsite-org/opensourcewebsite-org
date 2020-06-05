<?php
/**
 * @var $form \yii\widgets\ActiveForm
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 */

use app\modules\apiTesting\models\ApiTestResponse;

$items = [];
foreach (ApiTestResponse::responseCodesList() as $key => $value) {
    $items[$key] = $key.' '.$value;
}

?>

<?= $form->field($model, 'correct_response_code')->dropDownList($items); ?>

<?= $form->field($model, 'expected_response_body')->textarea(['class' => 'form-control', 'rows' => '10']); ?>
