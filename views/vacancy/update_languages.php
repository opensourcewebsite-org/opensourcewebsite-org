<?php

use app\components\helpers\ArrayHelper;
use app\models\Language;
use app\models\LanguageLevel;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use app\widgets\LanguagesWithLevelSelect\LanguagesWithLevelSelect;
use yii\bootstrap4\ActiveForm;
use yii\web\View;
use app\models\forms\LanguageWithLevelsForm;

/**
 * @var View $this
 * @var LanguageWithLevelsForm $model
 */

?>

<div class="modal-header">
    <h4 class="modal-title"><?= $this->title ?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<?php $form = ActiveForm::begin(); ?>
<div class="modal-body">
    <div class="row">
        <div class="col">
            <?= LanguagesWithLevelSelect::widget([
                'model' => $model,
                'form' => $form,
                'languages' => ArrayHelper::map(Language::find()->asArray()->all(),'id','name_ascii'),
                'languageLevels' => ArrayHelper::map(LanguageLevel::find()->asArray()->all(), 'id', 'description')
            ]) ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?= SaveButton::widget(); ?>
    <?= CancelButton::widget(['options' => ['data' => ['toggle' => 'modal']]])?>
</div>
<?php ActiveForm::end() ?>
