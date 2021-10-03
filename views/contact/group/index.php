<?php

use app\widgets\buttons\EditButton;
use app\widgets\ModalAjax;
use app\components\helpers\Html;
use yii\grid\GridView;
use app\models\Contact;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Contact groups');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="group-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <?= $this->render('../_navbar'); ?>
                    </div>
                    <div class="col-sm-6">
                        <div class="right-buttons float-right">
                            <?= ModalAjax::widget([
                                'header' => Yii::t('app', 'New Group'),
                                'toggleButton' => [
                                    'class' => 'btn btn-outline-success',
                                    'label' => Html::icon('add'),
                                    'style' => [
                                        'float' => 'right',
                                    ],
                                    'title' => Yii::t('app', 'New Group'),
                                ],
                                'url' => [
                                    '/contact/create-group',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'showHeader' => false,
                        'tableOptions' => ['class' => 'table table-hover table-condensed'],
                        'options' => [
                            'id' => 'contact-group-grid',
                        ],
                        'columns' => [
                            'name',
                            [
                                'class' => ActionColumn::class,
                                'contentOptions' => ['class' => 'lif-td'],
                                'template' => '{update}',
                                'buttons' => [
                                    'update' => static function ($url, $model, $key) {
                                        echo ModalAjax::widget([
                                            'id' => 'update-group' . $key,
                                            'header' => Yii::t('app', 'Edit group'),
                                            'url' => [
                                                '/contact/update-group',
                                                'id' => $key,
                                            ],
                                        ]);

                                        return EditButton::widget([
                                            'url' => '#',
                                            'options' => [
                                                'data-target' => '#update-group' . $key,
                                                'data-toggle' => 'modal',
                                                'class' => 'text-primary',
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
