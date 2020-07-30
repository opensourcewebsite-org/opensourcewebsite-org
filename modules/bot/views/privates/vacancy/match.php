<?php

use app\models\Resume;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::JOB_RESUME . ' ' . Yii::t('bot', 'Resume') ?>: <?= $name ?></b><br/>
<br/>
<?php if ($keywords != '') : ?>
# <i><?= $keywords ?></i><br/>
<br/>
<?php endif; ?>
<?php if ($skills) : ?>
<b><?= Yii::t('bot', 'Skills') ?>:</b><br/>
<br/>
<?= nl2br($skills) ?><br/>
<br/>
<?php endif; ?>
<?php if ($experiences) : ?>
<b><?= Yii::t('bot', 'Experiences') ?>:</b><br/>
<br/>
<?= nl2br($experiences) ?><br/>
<br/>
<?php endif; ?>
<?php if ($expectations) : ?>
<b><?= Yii::t('bot', 'Expectations') ?>:</b><br/>
<br/>
<?= nl2br($expectations) ?><br/>
<br/>
<?php endif; ?>
<?php if ($hourlyRate) : ?>
<b><?= Yii::t('bot', 'Min. hourly rate') ?>:</b> <?= $hourlyRate ?> <?= $currencyCode ?><br/>
<br/>
<?php endif; ?>
<b><?= Yii::t('bot', 'Remote work') ?></b>: <?= $remote_on == Resume::REMOTE_ON ? Yii::t('bot', 'Yes') : Yii::t('bot', 'No') ; ?><br/>
<br/>
<?php if ($user) : ?>
————<br/>
<br/>
<?php if ($user->provider_user_name) : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> @<?= $user->provider_user_name ?>
<?php else : ?>
<b><?= Yii::t('bot', 'Contact') ?>:</b> <a href="tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a>
<?php endif; ?>
<?php endif; ?>
