<?php

use yii\base\Model;
use yii\web\View;
use yii\bootstrap4\Html;

/**
 * @var View $this
 * @var Model $model
 * @var array $languageLevels
 * @var string $languageLevelFieldName
 * @var int|string $selected
 */
?>

    <?= Html::dropDownList($languageLevelFieldName, $selected, $languageLevels,
        [
            'prompt' => Yii::t('app', 'Select Language Level...'),
            'class' => ['form-control']
        ]
    ) ?>
