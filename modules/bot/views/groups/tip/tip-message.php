@<?= $toUser->getUsername() ?> <?= Yii::t('bot', 'was tipped') ?>:<br/>
<?php foreach($totalAmounts as $code => $amount) : ?>
<?= $amount ?> <?= $code ?><br/>
<?php endforeach; ?>
