<?= Yii::t('bot', 'Your last message in the group «{0}» was deleted because you are allowed to send messages until {1}', [$chat->title, $chatMember->limiter_date]) ?>.
