<?php

/* @var $model app\models\search\SupportGroupOutsideMessageSearch */
?>

<div class="news-item mb-2">
    <b><?= 'Client ' . $model->supportGroupBotClient->provider_bot_user_id ?></b><br>
    <?= $model->message ?> <small class="float-right"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></small>
</div>