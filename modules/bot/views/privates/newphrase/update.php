<?php
if ($isCreated) { ?>
<?= \Yii::t('bot', 'Phrase successfully added') ?> ✅
<?php } else { ?>
❗️  <?= \Yii::t('bot', 'Error. Phrase is already exists') ?>
<?php } ?>