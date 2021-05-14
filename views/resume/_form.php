<?php

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\Resume;
use yii\web\View;
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
                                <div class="input-group mb-3 align-items-start">
                                    <?= $form->field($model, 'location', ['options' => ['class' => 'form-group flex-grow-1']])
                                        ->textInput([
                                            'maxlength' => true,
                                            'id' => 'resume-location',
                                            'class' => 'form-control flex-grow-1'
                                        ])->label(false)
                                    ?>
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-info btn-flat map-btn"
                                                data-toggle="modal" data-target="map-modal"
                                                data-target-field-id="resume-location">
                                            <?= Yii::t('app', 'Map') ?>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <?= $form->field($model, 'search_radius')->textInput(['maxlength' => true, 'placeholder' => 0]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$js = <<<JS
$('.map-btn').on('click', function (){
    window.currencyExchangeLocationTargetField = $('#'+$(this).data('target-field-id'));
    $("#map-modal").modal('show');
})
JS;
$this->registerJs($js);



