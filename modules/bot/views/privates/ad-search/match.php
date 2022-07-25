<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::AD_OFFER ?> <b><?= Yii::t('bot', $model->getSectionName()) ?>: #<?= $model->id ?> <?= $model->title ?></b><br/>
<?php if ($model->description) : ?>
<br/>
<?= nl2br($model->description); ?><br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
<br/>
# <i><?= $keywords ?></i><br/>
<?php endif; ?>
<?php if ($model->price) : ?>
<br/>
<b><?= Yii::t('bot', 'Price') ?></b>: <?= $model->price ?> <?= $model->currency->code ?><br/>
<?php endif; ?>
<?php if ($user = $model->user->botUser) : ?>
————<br/>
<b><?= Yii::t('bot', 'Contact') ?></b>: <?= $user->getFullLink(); ?>
<?php endif; ?>
