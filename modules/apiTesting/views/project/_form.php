<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\helpers\Html as Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestProject */
/* @var $form yii\widgets\ActiveForm */
?>



<?php

/* @var $this yii\web\View */
/* @var $issue app\models\Issue */
/* @var $form yii\widgets\ActiveForm */
?>
<?=$this->render('../common/_project_tab', [
    'model' => $model
]); ?>
<div class="api-test-project-form">
    <?php
    $form = ActiveForm::begin(['fieldConfig' => [
        'options' => [
            'tag' => false,
        ],
    ]]);
    ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]); ?>

                            <?=$form->field($model, 'description')->textarea(['rows' => 10]); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'project_type')->radioList($model::projectTypes(), [
                                'item' => function ($index, $label, $name, $checked, $value) use ($model) {
                                    $radio = Html::radio($name, $checked, ['value' => $value, 'label' => $label]);
                                    return $radio.Html::tag('p',  $model::projecTypesDesription()[$value]);
                                }

                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget([
                        'url' => ['index']
                    ]); ?>
                    <?php if ( ! $model->isNewRecord):?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'id' => 'delete-project',
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
