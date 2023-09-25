<?php

use app\components\helpers\TimeHelper;

?>
<b><?= Yii::t('bot', 'Your Profile') ?></b><br/>
<?php if ($user->birthday || $user->gender || $user->sexuality) : ?>
<br/>
<?php if ($user->birthday) : ?>
<b><?= Yii::t('bot', 'Birthday') ?></b>: <?= $user->birthday ?><br/>
<?php endif; ?>
<?php if ($user->gender) : ?>
<b><?= Yii::t('bot', 'Gender') ?></b>: <?= Yii::t('bot', $user->gender->name) ?><br/>
<?php endif; ?>
<?php if ($user->sexuality) : ?>
<b><?= Yii::t('bot', 'Sexuality') ?></b>: <?= Yii::t('bot', $user->sexuality->name) ?><br/>
<?php endif; ?>
<?php endif; ?>
<?php if ($user->languages) : ?>
<br/>
<b><?=  Yii::t('bot', 'Languages') ?></b>:<br/>
<br/>
<?php foreach ($user->languages as $language) : ?>
  • <?= $language->getLabel() ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<?php if ($user->citizenships) : ?>
<br/>
<b><?= Yii::t('bot', 'Citizenships') ?></b>:<br/>
<br/>
<?php foreach ($user->citizenships as $citizenship) : ?>
  • <?= Yii::t('user', $citizenship->country->name) ?><br/>
<?php endforeach; ?>
<?php endif; ?>
<br/>
<b><?= Yii::t('bot', 'Timezone') ?></b>: <?= $user->getTimezoneName() ?><br/>
<?php if ($user->currency) : ?>
<b><?= Yii::t('bot', 'Currency') ?></b>: <?= $user->currency->code . ' - ' . $user->currency->name ?><br/>
<?php endif; ?>
