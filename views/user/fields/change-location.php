<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\buttons\DeleteButton;
use app\widgets\LocationPickerWidget\LocationPickerWidget;

$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <?= Yii::t('app', 'Change location'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($userLocation, 'location')
                                ->widget(LocationPickerWidget::class)
                                ->label(false);
                            ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['/account'],
                    ]); ?>
                    <?= DeleteButton::widget([
                        'url' => [
                            '/user/delete-location',
                        ],
                        'visible' => !$userLocation->isNewRecord,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
