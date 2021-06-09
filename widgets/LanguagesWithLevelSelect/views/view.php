<?php

use yii\bootstrap4\ActiveForm;
use yii\web\View;
use app\models\FormModels\LanguageWithLevelsForm;

/**
 * @var View $this
 * @var string $languageFieldName
 * @var string $languageLevelFieldName
 * @var string $label
 * @var string $id
 * @var array $languages
 * @var array $languageLevels
 * @var array $_params_
 * @var LanguageWithLevelsForm $model
 * @var ActiveForm|null $form
 */

$templateId = "{$id}-row-template";
?>
<template id="<?=$templateId?>">
    <?= $this->render('_row', array_merge($_params_, [
        'selectedLanguage' => '',
        'selectedLanguageLevel' => '',
    ])) ?>
</template>
<div class="card" id="<?= $id ?>">
    <div class="card-header d-flex p-0">
        <div class="card-title">
            <strong class="mx-3 mt-4"><?= $label ?></strong>
        </div>
        <div class="actions-col ml-auto p-2">
            <button type="button" class="btn btn-outline-success add-row-btn">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if ($model && $model->language_id): ?>
            <?php foreach($model->language_id as $key => $langId): ?>
                <?= $this->render('_row', array_merge($_params_, [
                    'selectedLanguage' => $langId,
                    'selectedLanguageLevel' => $model->language_level_id[$key],
                ])) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?= $this->render('_row', array_merge($_params_, [
                'selectedLanguage' => '',
                'selectedLanguageLevel' => '',
            ])) ?>
        <?php endif; ?>
    </div>
</div>
<?php
$formId = json_encode($form ? '#' . $form->getId() : null);
$selectLangLevelMessage = Yii::t('app', 'Please select Language Level');
$selectLangMessage = Yii::t('app', 'Please select Language');

$js = <<<JS
const selectLangMessage = '{$selectLangMessage}';
const selectLangLevelMessage = '{$selectLangLevelMessage}';
const template = $('#{$templateId}');
const mainNode = $('#{$id} .card-body');
const mainFormId = {$formId};

const validateOurWidget = function (e) {
    e.preventDefault();
    let error = false;
    mainNode.find('.language-row').each(function(){
        let langsDropdown = $(this).find('.languages-dropdown');
        let langsLevelsDropdown = $(this).find('.languages-levels-dropdown');

        if (langsDropdown.val() && !langsLevelsDropdown.val()) {
            error = true;
            langsLevelsDropdown.siblings('.help-block').html(selectLangLevelMessage);
        }
    });
    return !error;

}

if (mainFormId) {
    $(mainFormId).on('beforeSubmit', validateOurWidget);
}

$(document).on('click', '.add-row-btn', function () {
    mainNode.append(template.contents().clone());
})

$(document).on('click', '.remove-row-btn', function() {
    if (mainNode.find('.language-row').length > 1) {
        $(this).parent().parent().remove();
    }
})

JS;
$this->registerJs($js);
