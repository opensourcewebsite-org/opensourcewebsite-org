Hello, <?= $user->name ?>!
You requested a merge of your user account with <?= !empty($userToMerge->name) ? $userToMerge->name : 'noname' ?>`s account.
<a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['site/merge-accounts', 'token' => $mergeAccountsRequestToken]) ?>">
	Merge accounts
</a>
If you didn't create such request, just ignore this letter. 