<?php

use yii\base\Model;
use yii\bootstrap4\Html;
use yii\web\View;

/**
 * @var View $this
 * @var Model $model
 * @var array $languageLevels
 * @var string $languageLevelFieldName
 * @var int|string $selected
 */
?>

    <?= Html::dropDownList(
    $languageLevelFieldName,
    $selected,
    $languageLevels,
    [
            'prompt' => Yii::t('app', 'Select Language Level...'),
            'class' => ['form-control', 'languages-levels-dropdown'],
    ]
);
?>
