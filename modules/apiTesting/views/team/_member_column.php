<?php
/**
 * @var $this \yii\web\View
 * @var $model \app\modules\apiTesting\models\ApiTestTeam
 */

use yii\helpers\Html;

?>
<div>
    <div>
        <div>
            <?php if ($model->getIsOwner()):?>
                <strong>(OWNER)</strong>
            <?php else:?>
                <strong><?= $model->user->name == "" ? $model->user->email : $model->user->name; ?></strong>
            <?php endif; ?>

            <?php if ($model->getIsCurrentUser()):?>
                <span class="badge badge-success">YOU</span>
            <?php endif; ?>
            <?php if ($model->status == $model::STATUS_INVITED):?>
                <span class="badge badge-warning">WAITING FOR ACCEPT</span>
            <?php endif; ?>
            <?php if ($model->getIsCurrentUser()):?>
                <div class="float-right">
                    <?= Html::a('Leave', ['leave', 'id' => $model->project_id], ['class' => 'btn btn-danger']); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-gray">
            Given access <?=Yii::$app->formatter->asRelativeTime($model->invited_at, 'now'); ?>
        </div>
    </div>

</div>

