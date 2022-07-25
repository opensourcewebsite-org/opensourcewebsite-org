<?php
declare(strict_types=1);

use app\models\Company;
use app\widgets\buttons\AddButton;
use app\widgets\buttons\CancelButton;
use app\widgets\buttons\SaveButton;
use yii\base\Model;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var bool $hasModel
 * @var Model $model
 * @var Company $companyModel
 * @var string|null $attribute
 * @var array $companies
 * @var array $options
 * @var array $pluginOptions
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
            'pluginOptions' => $pluginOptions,
        ]) ?>
    <?php else: ?>
        <?= Select2::widget([
            'name' => $name,
            'data' => $companies,
            'value' => $value,
            'options' => $options,
            'pluginOptions' => $pluginOptions,
        ]); ?>
    <?php endif; ?>
    <div class="input-group-append">
        <?= AddButton::widget(['options' => [
            'id' => $addBtnId,
            'href' => Url::to(['/company-user/create-ajax']),
            'class' => 'btn btn-outline-success modal-btn-ajax'
        ]]) ?>
    </div>
</div>
<?php
$newCompanySubmitUrl = Url::to(['/company-user/create-ajax']);
$js = <<<JS
    const newCompanySubmitUrl = '{$newCompanySubmitUrl}';
    const formId = '#create-company-form';
    const controlId = '#{$controlId}';
    $(document).on('beforeSubmit', formId, function (e){
        e.preventDefault();
        $.post(newCompanySubmitUrl, $(formId).serialize(), function(res){
            if (res.id) {
                const newOption = new Option(res.name, res.id, true, true);
                $(controlId).append(newOption).val(res.id);
                $(formId).remove();
                $('#main-modal').modal('hide');
            } else {
                $(formId).yiiActiveForm('updateMessages', res.errors);
            }
        });
        return false;
    })

JS;
$this->registerJs($js);



