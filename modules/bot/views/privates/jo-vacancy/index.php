<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?php if ($companyName) : ?>
<b><?= Emoji::JO_COMPANY  . ' ' . $companyName . ' - ' ?><?= Emoji::JO_VACANCY . ' ' . Yii::t('bot', 'Vacancies') ?></b>
<?php else : ?>
<b><?= Emoji::JO_VACANCY . ' ' . Yii::t('bot', 'Your Vacancies') ?></b>
<?php endif; ?>
