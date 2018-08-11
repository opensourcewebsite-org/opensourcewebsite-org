<a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token'=>$user->password_reset_token]) ?>">
	Reset password
</a>