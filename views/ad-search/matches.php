<?php

declare(strict_types=1);

use app\components\helpers\Html;
use app\models\AdSearch;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var int $adOfferId
 */

$this->title = Yii::t('app', 'Matched Searches');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Offers'), 'url' =>['/ad-offer/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['/ad-offer/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="index">
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
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, AdSearch $matchModel) use ($model) {
                                        return Html::a(
                                            $matchModel->isNewMatch() ? Html::badge('info', 'new') : Html::icon('eye'),
                                            Url::to(['/ad-search/view-match', 'adOfferId' => $model->id, 'adSearchId' => $matchModel->id]),
                                            ['class' => 'btn btn-outline-primary float-right']
                                        );
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
