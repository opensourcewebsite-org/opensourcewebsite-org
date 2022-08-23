<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?= Emoji::AD_SEARCH ?> <b><?= Yii::t('bot', $model->getSectionName()) ?>: #<?= $model->id ?> <?= $model->title ?></b><br/>
<?php if ($model->description) : ?>
<br/>
<?= nl2br($model->description); ?><br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
<br/>
# <i><?= $keywords ?></i><br/>
<?php endif; ?>
<?php if ($user = $model->user->botUser) : ?>
————<br/>
<?= Emoji::RIGHT ?> <?= $user->getFullLink(); ?>
<?php endif; ?>
