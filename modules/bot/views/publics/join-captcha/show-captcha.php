<?php
/**
 * @var $chatName string
 * @var $firstName string
 * @var $lastName string
 * @var $secret integer
 */
?>
<?= Yii::t('bot', 'Welcome to group') . ' ' . $chatName . ',' ?><br/>
<?= $firstName . ' ' . $lastName ?><br/>
<?= Yii::t('bot', 'Please press') . ' ' . $secret . ' ' . Yii::t('bot', 'to proceed') ?><br/>


