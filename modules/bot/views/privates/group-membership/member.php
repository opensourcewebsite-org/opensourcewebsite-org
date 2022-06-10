<b><?= $chat->title ?></b><br/>
<br/>
<?= $chatMember->membership_date . ' - ' . $chatMember->user->getFullName() . ($chatMember->user->provider_user_name ? ' @' . $chatMember->user->provider_user_name : ''); ?><br/>
