<?php

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
 */
$templateId = "{$id}-row-template";
?>
<template id="<?=$templateId?>">
    <?= $this->render('_row', array_merge($_params_, [
        'selectedLanguage' => '',
        'selectedLanguageLevel' => '',
    ])) ?>
</template>
<label><?= $label ?></label>
<div class="card" id="<?= $id ?>">
    <div class="card-header d-flex p-0">
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
$js = <<<JS
const template = $('#{$templateId}');
const mainNode = $('#{$id} .card-body');

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


