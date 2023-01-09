<?php
/**
 * @var $transaction WalletTransaction
 * @var $fromUser User
 * @var $toUser User
 * @var $currency Currency
 */

use app\models\Currency;
use app\models\WalletTransaction;
use app\modules\bot\models\User;

?>
@<?= $fromUser->getUsername(); ?> <?= Yii::t('bot', 'tipped'); ?> @<?= $toUser->getUsername(); ?> <?= $transaction->getAmount() - WalletTransaction::TRANSACTION_FEE; ?> <?= $currency->code; ?>
<br/>

