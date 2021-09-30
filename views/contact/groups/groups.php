<?php

use app\components\grid\SortActionColumn;
use app\components\helpers\Icon;
use app\widgets\buttons\EditButton;
use app\widgets\ModalAjax;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Contact;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Contact groups');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="groups-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= ModalAjax::widget([
                                'header' => Yii::t('app', 'Add group'),
                                'url' => ['create-group'],
                                'toggleButton' => [
                                    'label' => Icon::ADD,
                                    'class' => 'btn btn-outline-success',
                                ],
                            ]) ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Users'), ['contact/index', 'view' => Contact::VIEW_USER], [
                                'class' => 'nav-link show',
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Virtuals'), ['contact/index', 'view' => Contact::VIEW_VIRTUALS], [
                                'class' => 'nav-link show'
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Groups'), ['contact/groups'], [
                                'class' => 'nav-link show active',
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'showHeader' => false,
                        'tableOptions' => ['class' => 'table table-hover table-condensed'],
                        'options' => [
                            'id' => 'contact-groups-grid',
                        ],
                        'columns' => [
                            'name',
                            [
                                'class' => SortActionColumn::class,
                                'contentOptions' => ['class' => 'lif-td'],
                                'sortUpUrl' => 'sort-up-group',
                                'sortDownUrl' => 'sort-down-group',
                            ],
                            [
                                'class' => ActionColumn::class,
                                'contentOptions' => ['class' => 'lif-td'],
                                'template' => '{update}',
                                'buttons' => [
                                    'update' => static function ($url, $model, $key) {
                                        echo ModalAjax::widget([
                                            'id' => 'change-group' . $key,
                                            'header' => Yii::t('app', 'Change group'),
                                            'url' => [
                                                'update-group',
                                                'id' => $key
                                            ],
                                        ]);
                                        return EditButton::widget([
                                            'url' => '#',
                                            'options' => [
                                                'data-target' => '#change-group' . $key,
                                                'data-toggle' => 'modal',
                                                'class' => 'text-primary'
                                            ],
                                        ]);
                                    },
                                ],
                            ],

                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
