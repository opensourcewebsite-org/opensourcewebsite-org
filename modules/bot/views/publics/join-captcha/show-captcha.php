<?php
/**
 * @var $user app\modules\bot\models\User
 */
 ?>

<?= Yii::t('bot', 'Welcome')?>, <a href = "tg://user?id=<?= $user->provider_user_id ?>"><?= $user->provider_user_first_name ?> <?= $user->provider_user_last_name ?></a><br/>
<?= Yii::t('bot', 'Press' . ' 👍 ' . 'to avoid ban' )?><br/>



