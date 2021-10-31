<?php

use app\models\Contact;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model Contact */
/* @var $form yii\widgets\ActiveForm */

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';

$form = ActiveForm::begin();
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'userIdOrName')
                                ->textInput([
                                    'data-old-value' => $model->getLinkUserId(),
                                    'value' => $model->getLinkUserId(),
                                ])
                                ->label($model->getAttributeLabel('userIdOrName') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')
                                ->textInput()
                                ->label($model->getAttributeLabel('name') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'is_real')->radioList(Contact::IS_REAL_LABELS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'relation')->radioList(Contact::RELATION_LABELS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'is_basic_income_candidate')->radioList(Contact::IS_BASIC_INCOME_CANDIDATE_LABELS); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'vote_delegation_priority')
                                ->textInput([
                                    'type' => 'number',
                                    'placeholder' => '0, ' . Yii::t('app', 'Deny'),
                                    'value' => ($model->vote_delegation_priority ?: ''),
                                ])
                                ->label($model->getAttributeLabel('vote_delegation_priority') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'debt_redistribution_priority')
                                ->textInput([
                                    'type' => 'number',
                                    'placeholder' => '0, ' . Yii::t('app', 'Deny'),
                                    'value' => ($model->debt_redistribution_priority ?: ''),
                                ])
                                ->label($model->getAttributeLabel('debt_redistribution_priority') . $labelOptional); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/contact/index') : Url::to(['/contact/view', 'id' => $model->id])?>
                    <?= CancelButton::widget([
                        'url' => $cancelUrl,
                    ]); ?>
                    <?= DeleteButton::widget([
                        'url' => [
                            '/contact/delete-contact',
                            'id' => $model->id,
                        ],
                        'visible' => !$model->isNewRecord && ((string)$model->user_id === (string)Yii::$app->user->id),
                    ]);?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
