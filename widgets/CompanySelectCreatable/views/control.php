<?php
declare(strict_types=1);

use app\widgets\buttons\AddButton;
use yii\base\Model;
use kartik\select2\Select2;
use yii\web\View;

/**
 * @var View $this
 * @var bool $hasModel
 * @var Model $model
 * @var string|null $attribute
 * @var array $companies
 * @var array $options
 * @var string|int $value
 * @var string|null $name
 * @var string $controlId
 */
 $addBtnId = $controlId.'-add-company-btn';
?>
<div class="input-group d-flex mb-3 align-items-start">
    <?php if ($hasModel): ?>
        <?= Select2::widget([
            'model' => $model,
            'attribute' => $attribute,
            'data' => $companies,
            'options' => $options,
        ]) ?>
    <?php else: ?>
        <?= Select2::widget([
            'name' => $name,
            'data' => $companies,
            'value' => $value,
            'options' => $options,
        ]); ?>
    <?php endif; ?>
    <div class="input-group-append">
        <?= AddButton::widget(['options' => ['id' => $addBtnId]]) ?>
    </div>
</div>


