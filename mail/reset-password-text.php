Hello<?= !empty($user->name) ? ', ' . $user->name : '' ?>!

Someone has requested a link to reset your password. To reset your password please visit the following link <?= $link ?>.

If you ignore this message, your password will not be changed.

Thanks,
<?= Yii::$app->name ?>
