<?php

declare(strict_types=1);

use app\models\AdSearch;
use app\models\search\AdSearchSearch;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use app\models\AdOffer;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var AdSearchSearch $searchModel
 */

$this->title = Yii::t('app', 'Searches');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveTab = (int)$searchModel->status === AdSearch::STATUS_ON;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item">
                                <?= Html::a(
    Yii::t('app', 'Active'),
    ['/ad-search/index', 'AdSearchSearch[status]' => AdSearch::STATUS_ON],
    [
                                        'class' => 'nav-link show ' . ($displayActiveTab ? 'active' : '')
                                    ]
);
                                ?>
                            </li>
                            <li class="nav-item">
                                <?= Html::a(
                                    Yii::t('app', 'Inactive'),
                                    ['/ad-search/index', 'AdSearchSearch[status]' => AdSearch::STATUS_OFF],
                                    [
                                        'class' => 'nav-link show ' . (!$displayActiveTab ? 'active' : ''),
                                    ]
                                );
                                ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <?= AddButton::widget([
                            'url' => ['create'],
                            'options' => [
                                'title' => Yii::t('app', 'New Search'),
                                'style' => [
                                    'float' => 'right',
                                ],
                            ],
                        ]); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'sectionName',
                                'label' => Yii::t('app', 'Section'),
                                'value' => function ($model) {
                                    return $model->sectionName;
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'title',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'max_price',
                                'content' => function (AdSearch $model) {
                                    return $model->max_price ? $model->max_price . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => 'Offers',
                                'value' => function (AdSearch $model) {
                                    return $model->getMatchesCount() ?
                                        Html::a(
                                            $model->getNewMatchesCount() ? Html::badge('info', 'new') : $model->getMatchesCount(),
                                            Url::to(['/ad-offer/show-matches', 'adSearchId' => $model->id])
                                        ) : '';
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                                'visible' => $displayActiveTab,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        return Html::a(Html::icon('eye'), $url, ['class' => 'btn btn-outline-primary']);
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
