<?php
/**
 * @var $chatName string
 * @var $firstName string
 * @var $lastName string
 * @var $provider_user_name string
 */
?>
<?= Yii::t('bot', 'Welcome to group') . ' ' . $chatName . ',' ?><br/>
<?= $provider_user_name ?? ($firstName . ' ' . $lastName) ?><br/>
<?= Yii::t('bot', 'Press' . ' 👍 ' . 'to avoid ban' )?><br/>



