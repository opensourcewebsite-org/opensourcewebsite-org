<?php

use app\models\Company;
use app\widgets\buttons\DeleteButton;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;

/**
 * @var View $this
 * @var Company $model
 */

$labelOptional = ' (' . Yii::t('app', 'optional') . ')';

$form = ActiveForm::begin(['id' => 'form']);
?>
<div class="form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'name')
                                ->textInput(); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'url')
                                ->textInput()
                                ->label($model->getAttributeLabel('url') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'address')
                                ->textInput()
                                ->label($model->getAttributeLabel('address') . $labelOptional); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($model, 'description')
                                ->textarea()
                                ->label($model->getAttributeLabel('description') . $labelOptional); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?php $cancelUrl = $model->isNewRecord ? Url::to('/company-user/index') : Url::to(['/company-user/view', 'id' => $model->id]) ?>
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
<?php ActiveForm::end() ?>
