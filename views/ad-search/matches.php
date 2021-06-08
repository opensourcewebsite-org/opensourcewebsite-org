<?php
declare(strict_types=1);

use app\models\AdSearch;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\grid\GridView;
/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var int $adOfferId
 */

$this->title = Yii::t('app', 'Ad Search Matches');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ad Offers'), 'url' =>['/ad-offer/index']];
$this->params['breadcrumbs'][] = ['label' => "#{$adOfferId}", 'url' =>['/ad-offer/view', 'id' => $adOfferId]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ad-search-index">
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
                                'attribute' => 'max_price',
                                'content' => function (AdSearch $model) {
                                    return $model->max_price ? $model->max_price . $model->currency->code : '';
                                }
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) use($adOfferId) {
                                        $url = Url::to(['/ad-search/view-match', 'adSearchId' => $model->id, 'adOfferId' => $adOfferId]);
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
