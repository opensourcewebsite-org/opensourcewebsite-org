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
 * @var int $adSearchId
 */

$this->title = Yii::t('app', 'Ad Offers Matches');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ad Search'), 'url' =>['/ad-search/index']];
$this->params['breadcrumbs'][] = ['label' => "#{$adSearchId}", 'url' =>['/ad-search/view', 'id' => $adSearchId]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ad-offer-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
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
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) use($adSearchId) {
                                        $url = Url::to(['/ad-offer/view-match', 'adSearchId' => $adSearchId, 'adOfferId' => $model->id,]);
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
