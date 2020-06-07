<?php
/**
 * @var $response \app\modules\apiTesting\models\ApiTestResponse
 * @var $this \yii\web\View
 */
?>

<span class="badge badge-<?=$response->getStatusLabel() == 'Success' ? 'success' : 'danger'; ?>">
    <?=$response->getStatusLabel(); ?>
</span>
&nbsp;
<strong><?= $response->request->methodString; ?></strong>
&nbsp;
<span class="text-<?=$response->codeTextStyle(); ?>">
    <?= $response->getCodeFormatted(); ?>
</span>
&nbsp;
<?= floor($response->time); ?> ms
&nbsp;
<?=Yii::$app->formatter->asShortSize($response->size, '2'); ?>

