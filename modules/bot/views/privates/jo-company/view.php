<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JO_COMPANY . ' ' . Yii::t('bot', 'Company') ?>: <?= $model->name ?></b><br/>
<br/>
<?php if ($model->description) : ?>
<?= nl2br($model->description) ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->address) : ?>
<b><?= Yii::t('bot', 'Address') ?>:</b> <?= $model->address ?><br/>
<br/>
<?php endif; ?>
<?php if ($model->url) : ?>
<b><?= Yii::t('bot', 'Website') ?>:</b> <?= $model->url ?><br/>
<br/>
<?php endif; ?>
