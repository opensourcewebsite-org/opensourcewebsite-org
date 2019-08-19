<?php

/* @var $model app\models\search\SupportGroupOutsideMessageSearch */
?>

<div class="news-item mb-2">
    <b><?= $model->showChatName() ?></b><br>
    <?= $model->message ?> <small class="float-right"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></small>
</div>