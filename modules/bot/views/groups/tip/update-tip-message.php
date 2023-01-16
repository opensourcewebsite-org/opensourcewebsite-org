@<?= $toUser->getUsername() ?> <?= Yii::t('bot', 'was tipped') ?>:<br/>
<?php foreach($tipTransactions as $transaction) : ?>
<?php if (isset($transaction)) : ?>
<?= Yii::t('bot', 'by') ?> @<?= $transaction->fromUser->getUsername() ?> (<?= $transaction->amount ?> <?= $transaction->currency->code ?> )<br/>
<?php endif; ?>
<?php endforeach; ?>
