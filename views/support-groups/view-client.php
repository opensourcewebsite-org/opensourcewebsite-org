<?php

use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupBotClient */
/* @var $sendMessage app\models\SupportGroupInsideMessage */

$this->title = "Client $model->provider_bot_user_id";
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-5">
                <?php if ($messages = $model->supportGroupOutsideMessage) {
                    foreach ($messages as $item) {
                        echo $item->getHtmlMessage() . "\n";
                    }
                } else {
                    echo 'No messages.';
                } ?>
                </div>

                <?php $form = ActiveForm::begin() ?>

                    <?= $form->field($sendMessage, 'message')->textarea() ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success float-right']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <?= DetailView::widget([
                'model'      => $model,
                'attributes' => [
                    'supportGroupClient.language_code',
                    'provider_bot_user_name',
                    [
                        'attribute' => 'provider_bot_user_name',
                        'visible'   => (empty($model->provider_bot_user_name)) ? false : true,
                    ],
                    [
                        'attribute' => 'provider_bot_user_first_name',
                        'visible'   => (empty($model->provider_bot_user_first_name)) ? false : true,
                    ],
                    [
                        'attribute' => 'provider_bot_user_last_name',
                        'visible'   => (empty($model->provider_bot_user_last_name)) ? false : true,
                    ],
                    [
                        'attribute' => 'last_message_at',
                        'visible'   => (empty($model->last_message_at)) ? false : true,
                        'format' => 'relativeTime',
                    ],
                    [
                        'attribute' => 'location_at',
                        'visible'   => (empty($model->location_at)) ? false : true,
                        'format' => 'relativeTime',
                    ],
                ],
            ]) ?>
        </div>
    </div>
</div>