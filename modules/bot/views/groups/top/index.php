<?php

use app\modules\bot\models\User;
use app\modules\bot\components\helpers\Emoji;

?>
<b><?= Emoji::LIKE . ' ' . Yii::t('bot', 'Awesome members'); ?>:</b><br/>
<br/>
<?php foreach ($users as $user) : ?>
<?= User::getFullLinkByProviderUserId($user['provider_user_id']); ?> (<?= $user['rating']; ?>)<br/>
<?php endforeach; ?>
