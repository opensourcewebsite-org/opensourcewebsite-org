<p>
    Hello<?= !empty($user->name) ? ", {$user->name}!" : '!' ?>
</p>

<p>
    You requested a merge of your user account with <?= !empty($userToMerge->name) ? $userToMerge->name : 'noname' ?>`s account.
</p>

<p>
    <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(['site/merge-accounts', 'token' => $mergeAccountsRequestToken]) ?>">
        Merge accounts
    </a>
</p>

<p>
    If you didn't mean to do it, then you can just ignore this email.
</p>
