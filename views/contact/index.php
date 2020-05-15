<?php

use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Contact;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Contacts');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="contact-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['contact/create'],
                                'options' => [
                                    'title' => 'New Contact',
                                ]
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Users'), ['contact/index', 'view' => Contact::VIEW_USER], [
                                'class' => 'nav-link show ' . ((int) $view === Contact::VIEW_USER ? 'active' : ''),
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Virtuals'), ['contact/index', 'view' => Contact::VIEW_VIRTUALS], [
                                'class' => 'nav-link show ' . ((int) $view === Contact::VIEW_VIRTUALS ? 'active' : ''),
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
                                'label' => 'Name',
                                'value' => function (Contact $data) {
                                    return Html::a($data->getContactName(), ['/contact/view', 'id' => $data->id]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'is_real',
                                'value' => function ($model) {
                                    return $model->is_real ? 'Real' : '';
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Relation'),
                                'value' => function ($model) {
                                    return Yii::t('app', Contact::RELATIONS[$model->relation]['title']);
                                },
                                'format' => 'html',
                            ],
                           /* 'relation',*/
                            'vote_delegation_priority',
                            'debt_redistribution_priority',
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
