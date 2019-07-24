<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="support-group-exchange-rate-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Name (optional)'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'buying_rate')->textInput(['maxlength' => true])->label('Buying Rate (optional)'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'selling_rate')->textInput(['maxlength' => true])->label('Selling Rate (optional)'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'is_default')->checkbox(); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']); ?>
                    <?= Html::a(Yii::t('app', 'Cancel'), ['index', 'supportGroupId' => $supportGroupId], [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('app', 'Cancel'),
                    ]); ?>
                    <?php if (!$model->isNewRecord && $model->created_by === Yii::$app->user->id) : ?>
                        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id, 'supportGroupId' => $supportGroupId], [
                            'class' => 'btn btn-danger float-right',
                            'id' => 'delete-exchange-rate'
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->registerJs('$("#delete-exchange-rate").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('app', 'Are you sure you want to delete this exchange rate?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "' . Yii::$app->urlManager->createUrl(['/support-group-exchange-rate']) . '";
            }
            else {
                alert("' . Yii::t('app', 'Sorry, there was an error while trying to delete the exchange rate.') . '");
            }
        });
    }
    
    return false;
});'); ?>