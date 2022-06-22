<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::AD_OFFER . ' ' . Yii::t('bot', $model->getSectionName()) ?>: <?= $model->title ?></b><br/>
<br/>
<?php if ($model->description !== null) : ?>
<?= nl2br($model->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($model->price) : ?>
<b><?= Yii::t('bot', 'Price') ?>:</b> <?= $model->price ?> <?= $model->currency->code ?><br/>
<br/>
<?php endif; ?>
<?php if ($user) : ?>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <?= $user->getFullLink(); ?>
<?php endif; ?>
