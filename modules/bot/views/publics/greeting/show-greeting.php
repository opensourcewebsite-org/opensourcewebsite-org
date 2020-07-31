<?php
/**
 * @var $user app\modules\bot\models\User
 */

if ($user->provider_user_name) {
    $userLink = '@' . $user->provider_user_name;
} else {
    $userLink = '<a href="tg://user?id=' . $user->provider_user_id .'">' . $user->provider_user_first_name . ' ' . $user->provider_user_last_name . '</a>';
}
?>
<?= Yii::t('bot', 'Welcome') ?>, <?= $userLink ?>!<br/>
