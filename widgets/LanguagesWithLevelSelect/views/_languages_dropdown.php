<?php

use yii\base\Model;
use yii\web\View;
use yii\bootstrap4\Html;

/**
 * @var Model|null $model
 * @var View $this
 * @var string $languageFieldName
 * @var array $languages
 * @var $selected
 *
 */
?>
    <?= Html::dropDownList(
        $languageFieldName, $selected, $languages,
        [
            'prompt' => Yii::t('app', 'Select Language...'),
            'class' => ['form-control', 'languages-dropdown']
        ]
    )?>
