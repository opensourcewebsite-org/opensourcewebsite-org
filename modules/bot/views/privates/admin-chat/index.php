<b><?= $chatTitle ?></b><br/>
<br/>
<?= Yii::t('bot', 'Select a feature to manage the group') ?>.<br/>
<br/>
<?= Yii::t('bot', 'Administrators who can manage the group') ?>:<br/>
<?php
foreach ($admins as $user) {
    if ($user->provider_user_name) {
        $userLinks[] = '@' . $user->provider_user_name;
    } else {
        $userLinks[] = '<a href="tg://user?id=' . $user->provider_user_id .'">' . $user->provider_user_first_name . ' ' . $user->provider_user_last_name . '</a>';
    }
}
?>
<?= implode(', ', $userLinks); ?>
