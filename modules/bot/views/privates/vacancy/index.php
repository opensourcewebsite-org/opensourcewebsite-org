<?php if ($companyName) : ?>
<b><?= $companyName . ' - ' ?><?= Yii::t('bot', 'Vacancies') ?></b>
<?php else : ?>
<b><?= Yii::t('bot', 'Your Vacancies') ?></b>
<?php endif; ?>
