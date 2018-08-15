To reset your password please visit the following link  
<a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]) ?>">
	Reset password
</a>