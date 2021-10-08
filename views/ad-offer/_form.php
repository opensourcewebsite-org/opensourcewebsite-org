<?php

use app\models\AdOffer;
use app\models\AdSection;
use app\models\Currency;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\CurrencySelect\CurrencySelect;
use app\widgets\AdKeywordsSelect\AdKeywordsSelect;
use app\widgets\LocationPickerWidget\LocationPickerWidget;
use app\widgets\buttons\SubmitButton;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var AdOffer $model
 * @var Currency[] $currencies
 */

$showLocation = $model->location || $model->isNewRecord;
$labelOptional = ' (' . Yii::t('app', 'optional') . ')';
$iconPrivate = '<i class="far fa-eye-slash" title="' . Yii::t('app', 'Private') . '"></i> ';

$form = ActiveForm::begin(['id' => 'form']);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'section')->dropDownList(AdSection::getAdOfferNames(), ['prompt' => 'Select Section']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'title')->textInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'description')->textarea() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'price')->textInput() ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'currency_id')->widget(CurrencySelect::class); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?php $model->keywordsFromForm = $model->getKeywordsFromForm() ?>
                            <?= $form->field($model, 'keywordsFromForm')->widget(AdKeywordsSelect::class, ) ?>
                        </div>
                    </div>

                    <div class="row location-row">
                        <div class="col">
                            <?= $form->field($model, 'location')
                                ->widget(LocationPickerWidget::class)
                                ->label($iconPrivate . $model->getAttributeLabel('location'))
                            ?>
                        </div>
                    </div>
                    <div class="row location-row">
                        <div class="col">
                            <?= $form->field($model, 'delivery_radius')
                                ->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 0 . ', ' . Yii::t('app', 'No delivery'),
                                    'value' => ($model->delivery_radius ?: ''),
                                ])
                                ->label($iconPrivate . $model->getAttributeLabel('delivery_radius') . ', km' . $labelOptional)
                            ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SubmitButton::widget() ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/ad-offer/index') : Url::to(['/ad-offer/view', 'id' => $model->id])?>
                    <?= CancelButton::widget(['url' => $cancelUrl]); ?>
                    <?php if (!$model->isNewRecord): ?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'method' => 'post'
                                ]
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
