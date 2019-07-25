<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ButtonDropdown;
use app\components\helpers\SupportGroupHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', SupportGroupHelper::getExchangeRateCommandType($type));
$this->params['breadcrumbs'][] = ['label' => 'Support Groups', 'url' => ['support-groups/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="support-group-exchange-rate-command-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= Html::a('<i class="fa fa-plus"></i>', ['support-group-exchange-rate-command/create', 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type], [
                                'class' => 'btn btn-outline-success',
                                'title' => $this->title,
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => 'Command',
                                'value' => function ($data) {
                                    return $data->command ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{all}',
                                'buttons' => [
                                    'all' => function ($url, $model, $key) use ($supportGroupExchangeRateId, $type) {
                                        return ButtonDropdown::widget([
                                                'encodeLabel' => false,
                                                'label' => '<i class="fas fa-cog"></i>',
                                                'dropdown' => [
                                                    'items' => [
                                                        [
                                                            'label' => Yii::t('app', 'Edit'),
                                                            'url' => ['support-group-exchange-rate-command/update', 'id' => $key, 'supportGroupExchangeRateId' => $supportGroupExchangeRateId, 'type' => $type],
                                                            'linkOptions' => ['class' => 'dropdown-item'],
                                                        ],
                                                    ],
                                                ],
                                                'options' => [
                                                    'class' => 'btn-default',
                                                ],
                                        ]);
                                    }
                                ],
                            ],
                        ],
                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'options' => [
                                'class' => 'pagination float-right',
                            ],
                            'linkContainerOptions' => [
                                'class' => 'page-item',
                            ],
                            'linkOptions' => [
                                'class' => 'page-link',
                            ],
                            'maxButtonCount' => 5,
                            'disabledListItemSubTagOptions' => [
                                'tag' => 'a',
                                'class' => 'page-link',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
