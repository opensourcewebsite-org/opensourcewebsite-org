<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?php if ($companyName) : ?>
<b><?= $companyName . ' - ' ?><?= Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Vacancies') ?></b>
<?php else : ?>
<b><?= Emoji::JOB_VACANCY . ' ' . Yii::t('bot', 'Your Vacancies') ?></b>
<?php endif; ?>
