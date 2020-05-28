<?php

use app\widgets\buttons\CancelButton;
use app\widgets\buttons\DeleteButton;
use app\widgets\buttons\SaveButton;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'Create contact group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts group'), 'url' => ['contact/group']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')
             ->textInput()
             ->label(Yii::t('app', 'Name')); ?>

    <?= SaveButton::widget(); ?>
    <?= CancelButton::widget([
        'url' => 'groups'
    ]); ?>
    <?php
    if (!$model->isNewRecord) {
        echo DeleteButton::widget([
            'url' => ['delete-group', 'id' => $model->id]
        ]);
    } ?>
<?php ActiveForm::end(); ?>
</div>
