<?php

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\Resume;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use app\widgets\LocationPickerWidget\LocationPickerWidget;use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Resume */
/* @var $currencies Currency[] */


$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
?>
    <div class="currency-exchange-order-form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'name')->textInput() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'remote_on')->checkbox() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'min_hourly_rate')->textInput() ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'currency_id')->dropDownList(ArrayHelper::map($currencies, 'id', 'name')) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'location')->widget(LocationPickerWidget::class) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'search_radius')->textInput(['maxlength' => true, 'placeholder' => 0]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <?= SaveButton::widget(); ?>
                        <?= CancelButton::widget(['url' => '/resume']); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>




