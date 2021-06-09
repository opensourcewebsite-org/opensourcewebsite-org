<?php
declare(strict_types=1);

use app\models\search\AdOfferSearch;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;
use app\models\AdOffer;
/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var AdOfferSearch $searchModel
 */

$this->title = Yii::t('app', 'Ad Offers');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveOffers = $searchModel->status === AdOfferSearch::STATUS_ON;

?>
<div class="ad-offer-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item mx-1">
                            <?= Html::a(Yii::t('app', 'Active'),
                                ['/ad-offer/index', 'AdOfferSearch[status]' => AdOffer::STATUS_ON],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOffers ? 'active' : '')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item  mx-1">
                            <?= Html::a(Yii::t('app', 'Inactive'),
                                ['/ad-offer/index', 'AdOfferSearch[status]' => AdOffer::STATUS_OFF],
                                [
                                    'class' => 'nav-link show ' .
                                        ($displayActiveOffers ? '' : 'active')
                                ]);
                            ?>
                        </li>
                        <li class="nav-item align-self-center mr-4 mx-1">
                            <?= AddButton::widget([
                                'url' => ['create'],
                                'options' => [
                                    'title' => 'New Ad Offer',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'id',
                            'sectionName',
                            'title',
                            [
                                'attribute' => 'price',
                                'content' => function (AdOffer $model) {
                                    return $model->price ? $model->price . $model->currency->code : '';
                                }
                            ],
                            [
                                'label' => 'Offers',
                                'enableSorting' => false,
                                'format' => 'raw',
                                'value' => function(AdOffer $model){
                                    return $model->getMatches()->count() ?
                                        Html::a(
                                            $model->getMatches()->count(),
                                            Url::to(['/ad-search/show-matches', 'adOfferId' => $model->id]),
                                        ) : '';
                                }
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
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
