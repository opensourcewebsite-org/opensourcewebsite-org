<?php
/**
 * @var $model \app\modules\apiTesting\models\ApiTestRequest
 */
?>
<?php if ( ! $model->labels) : ?>
    No labels added
<?php endif;

use app\components\helpers\Icon;
use app\widgets\ModalAjax;
use yii\helpers\Url;

?>

<?php foreach ($model->labels as $label):?>
    <span class="badge badge-primary"><?=$label->name; ?></span>
<?php endforeach; ?>

<?= ModalAjax::widget([
    'id' => 'add-label',
    'header' => Yii::t('user', 'Add label'),
    'closeButton' => false,
    'toggleButton' => [
        'label' => Icon::EDIT,
        'class' => 'btn btn-outline-success ml-4',
    ],
    'url' => Url::to(['labels-manage', 'id' => $model->id]),
    'ajaxSubmit' => true,
]); ?>
