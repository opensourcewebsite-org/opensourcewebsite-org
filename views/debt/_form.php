<?php

use app\models\Debt;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use janisto\timepicker\TimePicker;
use app\widgets\selects\CurrencySelect;
use yii\web\JsExpression;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model app\models\Debt */
/* @var $form yii\widgets\ActiveForm */

$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'direction')->radioList(Debt::DIRECTION_LABELS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">      
                            <?=$form->field($model, 'counter_user_id')->widget(Select2::class, [
                                'options' => [
                                    'formatInputTooShort'=>'test',
                                     'prompt' => 'Select...' ,
                                     'placeholder' => 'Select resutls...'
                                ],
                                'pluginOptions' => [
                                    'minimumInputLength' => 2,
                                    'ajax' => [
                                        'url' =>  Url::to(['ajax-users']),
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(params) { return {q:params.term,  page: params.page || 1 }; }')
                                    ],
                                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                    'templateResult' => new JsExpression('function(user) { return user.username; }'),
                                    'templateSelection' => new JsExpression('function (user) { return user.username; }'),
                                ],
                            ]);
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'amount')
                                ->textInput([
                                    'autocomplete' => 'off',
                                ]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'currency_id')->widget(CurrencySelect::class); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget([
                        'text' => Yii::t('app', 'Add'),
                        'options' => [
                            'title' => Yii::t('app', 'Add'),
                        ],
                    ]); ?>
                    <?= CancelButton::widget([
                        'url' => ['/debt'],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
