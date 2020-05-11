<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupExchangeRate */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="support-group-exchange-rate-command-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'command')->textInput(['maxlength' => true])->hint('Use "??" for an amount from a user.'); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['index', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type]
                    ]); ?>
                    <?php if (!$model->isNewRecord) : ?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id, 'supportGroupExchangeRateId' =>
                                $supportGroupExchangeRateId, 'type' => $type],
                            'id' => 'delete-exchange-rate-command',
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php $this->registerJs('$("#delete-exchange-rate-command").on("click", function(event) {
    event.preventDefault();
    var url = $(this).attr("href");

    if (confirm("' . Yii::t('app', 'Are you sure you want to delete this exchange rate command?') . '")) {
        $.post(url, {}, function(result) {
            if (result == "1") {
                location.href = "' . Yii::$app->urlManager->createUrl(['/support-group-exchange-rate-command']) . '";
            }
            else {
                alert("' . Yii::t('app', 'Sorry, there was an error while trying to delete the exchange rate command.') . '");
            }
        });
    }

    return false;
});'); ?>
