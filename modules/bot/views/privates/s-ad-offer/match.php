<?php

use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::AD_SEARCH . ' ' . Yii::t('bot', $sectionName) ?>: <?= $adSearch->title ?></b><br/>
<br/>
<?php if ($adSearch->description !== null) : ?>
<?= nl2br($adSearch->description); ?><br/>
<br/>
<?php endif; ?>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($user) : ?>
————<br/>
<br/>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <?= $user->getFullLink(); ?>
<?php endif; ?>
