<?php

use yii\bootstrap4\Html;
use yii\web\View;


/**
 * @var View $this
 * @var string $languageFieldName
 * @var string $languageLevelFieldName
 * @var string $label
 * @var string $id
 * @var array $languages
 * @var array $languageLevels
 */
$templateId = "{$id}-row-template";
?>
<template id="<?=$templateId?>">
    <div class="row mb-2">
        <div class="col d-flex">
            <div class="col">
                <?= Html::dropDownList(
                    $languageFieldName, '', $languages,
                    [
                        'prompt' => Yii::t('app', 'Select Language...'),
                        'class' => ['form-control']
                    ]
                )?>
            </div>
            <div class="col">
                <?= Html::dropDownList($languageLevelFieldName, '', $languageLevels,
                    [
                        'prompt' => Yii::t('app', 'Select Language Level...'),
                        'class' => ['form-control']
                    ]
                ) ?>
            </div>
        </div>
        <div class="col flex-grow-0">
            <button type="button" class="btn btn-outline-danger remove-row-btn"><i class="fa fa-minus"></i></button>
        </div>
    </div>
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

    </div>
</div>
<?php
$js = <<<JS
$(document).on('click', '.add-row-btn', function () {
    const template = $('#{$templateId}');
    const mainNode = $('#{$id} .card-body');
    console.log(mainNode);
    mainNode.append(template.contents().clone());
})

$(document).on('click', '.remove-row-btn', function() {
    $(this).parent().parent().remove();
})
JS;
$this->registerJs($js);


