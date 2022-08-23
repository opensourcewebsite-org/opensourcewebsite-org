<?php

use app\modules\bot\components\helpers\Emoji;

?>
<?php if ($companyName) : ?>
<?= Emoji::JO_COMPANY ?> <b><?= $companyName ?></b><br/>
<br/>
<?= Emoji::JO_VACANCY ?> <b><?= Yii::t('bot', 'Vacancies') ?></b>
<?php else : ?>
<?= Emoji::JO_VACANCY ?> <b><?= Yii::t('bot', 'Your vacancies') ?></b>
<?php endif; ?>
