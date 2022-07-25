<?php

declare(strict_types=1);

use yii\helpers\Html;
use app\components\Converter;
use yii\widgets\LinkPager;
use yii\grid\GridView;

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="issue-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => [
                            'class' => 'table table-hover',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'key',
                                'label' => Yii::t('app', 'Name'),
                                'value' => function ($model) {
                                    return Html::a($model->key, ['/setting/view', 'key' => $model->key]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'value',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format'    => [
                                    'relativeTime',
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
