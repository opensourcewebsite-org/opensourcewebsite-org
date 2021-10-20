<?php

use app\widgets\buttons\AddButton;
use app\components\helpers\Html;
use yii\grid\GridView;
use app\models\Contact;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $view int */

$this->title = Yii::t('app', 'Contacts');
?>
<div class="contact-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <?= $this->render('_navbar'); ?>
                    </div>
                    <div class="col-sm-6">
                        <div class="right-buttons float-right">
                            <?= AddButton::widget([
                                'url' => ['contact/create'],
                                'options' => [
                                    'title' => 'New Contact',
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => Yii::t('app', 'Name'),
                                'value' => function (Contact $model) {
                                    return Html::a($model->getContactName(), ['contact/view', 'id' => $model->id]);
                                },
                                'enableSorting' => false,
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Identification'),
                                'attribute' => 'is_real',
                                'value' => static function (Contact $model) {
                                    return $model->getIsRealBadge();
                                },
                                'enableSorting' => false,
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'relation',
                                'value' => static function (Contact $model) {
                                    return $model->getRelationBadge();
                                },
                                'enableSorting' => false,
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'vote_delegation_priority',
                                'enableSorting' => false,
                                'value' => static function (Contact $model) {
                                    return $model->vote_delegation_priority ?: Html::badge('secondary', Yii::t('app', 'DENY'));
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'debt_redistribution_priority',
                                'enableSorting' => false,
                                'value' => static function (Contact $model) {
                                    return $model->debt_redistribution_priority ?: Html::badge('secondary', Yii::t('app', 'DENY'));
                                },
                                'format' => 'html',
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);

                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary',]);
                                    },
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
