<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Debts');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="contact-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => 'Currency',
                                'value' => function ($data) {
                                    return $data->currency->code ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Deposit',
                                'value' => function ($data) {
                                    return Html::a($data->id, ['/debt/view', 'id' => $data->id]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'Credit',
                                'value' => function ($data) {
                                    return Html::a($data->id, ['/debt/view', 'id' => $data->id]);
                                },
                                'format' => 'html',
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
