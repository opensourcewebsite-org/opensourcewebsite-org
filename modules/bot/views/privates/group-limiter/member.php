<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->limiter_date . ' - ' . $chatMember->user->getFullName() . ($chatMember->user->provider_user_name ? ' @' . $chatMember->user->provider_user_name : ''); ?><br/>
