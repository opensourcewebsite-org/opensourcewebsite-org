<?php

use app\components\helpers\ArrayHelper;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contact */

$this->title = Yii::t('app', 'Create contact group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contacts group'), 'url' => ['contact/group']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'contact_group_ids')->widget(Select2::class, [
            'data'          => ArrayHelper::map(Yii::$app->user->identity->getContactGroups()->orderBy('position')->all(), 'id', 'name'),
            'showToggleAll' => false,
            'pluginOptions' => [
                'tags' => true,
            ],
            'options'       => [
                'multiple' => true,
            ],
        ])->label('Groups'); ?>

    <?= SaveButton::widget(); ?>
    <?= CancelButton::widget([
        'url' => 'groups'
    ]); ?>
<?php ActiveForm::end(); ?>
</div>
