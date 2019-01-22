<?php

use yii\widgets\DetailView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupBotClient */
/* @var $sendMessage app\models\SupportGroupInsideMessage */
/* @var $searchModel app\models\search\SupportGroupOutsideMessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = "Client $model->provider_bot_user_id";
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = [
    'label' => 'Languages',
    'url'   => ['clients-languages', 'id' => $model->supportGroupClient->support_group_id],
];
$this->params['breadcrumbs'][] = [
    'label' => 'Clients Lists',
    'url'   => [
        'clients-list',
        'id'       => $model->supportGroupClient->support_group_id,
        'language' => $model->supportGroupClient->language_code,
    ],
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-5">
                    <?php \yii\widgets\Pjax::begin() ?>
                    <?= \yii\widgets\ListView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'layout' => "<div class='items-message-block'>{items}</div>\n{pager}",
                        'itemView' => 'clients/_messages',
                        'pager'        => [
                            'options'                       => [
                                'class' => 'pagination mt-4',
                            ],
                            'linkContainerOptions'          => [
                                'class' => 'page-item',
                            ],
                            'linkOptions'                   => [
                                'class' => 'page-link',
                            ],
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a', 'class' => 'page-link',
                            ],
                        ],
                    ]) ?>
                    <?php \yii\widgets\Pjax::end() ?>
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
            <div class="card-body">
                <?= DetailView::widget([
                    'model'      => $model,
                    'options'    => [
                        'tag' => 'ul',
                    ],
                    'template'   => '<li>{label}: {value}</li>',
                    'attributes' => [
                        'supportGroupClient.language_code',
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
                            'format'    => 'relativeTime',
                        ],
                        [
                            'attribute' => 'location_at',
                            'format'    => 'html',
                            'visible'   => (empty($model->location_at)) ? false : true,
                            'value'     => function ($model) {
                                return Html::a('(' . Yii::$app->formatter->asRelativeTime($model->location_at) . ')', '#');
                            },
                        ],
                    ],
                ]) ?>
                <?= $this->render('clients/_edit_description', ['model' => $model]) ?>
                <div class="mb-5">
                    <?= $model->description ?>
                </div>
            </div>
        </div>
    </div>
</div>