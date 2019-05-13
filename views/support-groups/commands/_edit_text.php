<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\SupportGroupCommandText;
use yii\helpers\Url;

/* @var $textModel null|app\models\SupportGroupCommandText */
/* @var $model app\models\SupportGroupCommand */
/* @var $lang app\models\SupportGroupLanguage */

?>

    <div class="text-right">
        <a class="btn btn-light" href="#" title="Edit" data-toggle="modal"
           data-target="#modalLanguage<?= $lang->id ?>"><i class="fas fa-edit"></i></a>
    </div>
<?php $form = ActiveForm::begin([
    'action' => Url::to(['text-update', 'id' => (!$textModel) ? null : $textModel->id]),
]) ?>
    <div class="modal fade" id="modalLanguage<?= $lang->id ?>" tabindex="-1" role="dialog"
         aria-labelledby="modalLanguageTitle" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit <?= $model->command ?>
                        : <?= $lang->languageCode->name_ascii ?></h5>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <?= $form->field(new SupportGroupCommandText(), 'text')->textarea([
                        'value' => (!$textModel) ? '' : $textModel->text, 'rows' => 3,
                    ]) ?>
                    <?= $form->field(new SupportGroupCommandText(), 'language_code')
                        ->hiddenInput(['value' => $lang->language_code])
                        ->label(false) ?>
                    <?= $form->field(new SupportGroupCommandText(), 'support_group_command_id')
                        ->hiddenInput(['value' => $model->id])
                        ->label(false) ?>
                </div>
                <div class="card-footer text-left">
                    <button type="submit" class="btn btn-success">Save</button>
                    <a class="btn btn-secondary" href="#" data-dismiss="modal" title="Cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>