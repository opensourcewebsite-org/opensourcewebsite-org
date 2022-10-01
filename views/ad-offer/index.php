<?php

declare(strict_types=1);

use app\components\helpers\Html;
use app\models\AdOffer;
use app\models\search\AdOfferSearch;
use app\widgets\buttons\AddButton;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var AdOfferSearch $searchModel
 */

$this->title = Yii::t('app', 'Offers');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveTab = $searchModel->status === AdOfferSearch::STATUS_ON;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card table-overflow">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item">
                                <?= Html::a(
    Yii::t('app', 'Active'),
    ['/ad-offer/index', 'AdOfferSearch[status]' => AdOffer::STATUS_ON],
    ['class' => 'nav-link show ' . ($displayActiveTab ? 'active' : '')]
); ?>
                            </li>
                            <li class="nav-item">
                                <?= Html::a(
                                    Yii::t('app', 'Inactive'),
                                    ['/ad-offer/index', 'AdOfferSearch[status]' => AdOffer::STATUS_OFF],
                                    ['class' => 'nav-link show ' . (!$displayActiveTab ? 'active' : '')]
                                ); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <?= AddButton::widget([
                            'url' => ['create'],
                            'options' => [
                                'title' => Yii::t('app', 'New Offer'),
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
                        //'filterModel' => $searchModel,
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
                                'attribute' => 'price',
                                'content' => function (AdOffer $model) {
                                    return $model->price ? $model->price . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => 'Offers',
                                'value' => function (AdOffer $model) {
                                    return ($matchesCount = $model->getMatches()->count()) ?
                                        Html::a(
                                            $model->getNewMatches()->exists() ? Html::badge('info', 'new') : $matchesCount,
                                            Url::to(['/ad-search/matches', 'adOfferId' => $model->id]),
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
                                        return Html::a(Html::icon('eye'), $url, ['class' => 'btn btn-outline-primary float-right']);
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
