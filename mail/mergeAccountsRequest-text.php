Hello<?= !empty($user->name) ? ", {$user->name}!" : '!' ?>
You requested a merge of your user account with <?= !empty($userToMerge->name) ? $userToMerge->name : 'noname' ?>`s account.
To confirm this request please go by the following link: <?= Yii::$app->urlManager->createAbsoluteUrl(['site/merge-accounts', 'token' => $mergeAccountsRequestToken]) ?>
If you didn't mean to do it, then you can just ignore this email.
