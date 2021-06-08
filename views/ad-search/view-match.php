<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\AdSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/**
 * @var View $this
 * @var AdSearch $model
 * @var int $adOfferId
 */

$this->title = Yii::t('app', 'Ad Search') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ad Offers'), 'url' => ['/ad-offer/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $adOfferId, 'url' => ['/ad-offer/view', 'id' => $adOfferId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Ad Search'), 'url' => ['/ad-search/show-matches', 'adOfferId' => $adOfferId]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
    <div class="ad-search-view">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'sectionName',
                                    'title',
                                    'description:ntext',
                                    [
                                        'label' => Yii::t('app', 'Keywords'),
                                        'visible' => (bool)$model->keywords,
                                        'value' => function () use ($model) {
                                            $text = '';

                                            foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                                $text .= '<small class="badge badge-primary">' . $keyword . '</small>&nbsp';
                                            }

                                            return $text;
                                        },
                                        'format' => 'raw',
                                    ],
                                    [
                                        'attribute' => 'max_price',
                                        'value' => $model->max_price ? $model->max_price . ' ' . $model->currency->code : '',
                                    ],
                                    [
                                        'attribute' => 'location',
                                        'visible' => (bool)$model->location,
                                        'value' => function () use ($model) {
                                            return Html::a(
                                                $model->location,
                                                Url::to(['view-location', 'id' => $model->id]),
                                                ['class' => 'modal-btn-ajax']
                                            );
                                        },
                                        'format' => 'raw',
                                    ],
                                    [
                                        'attribute' => 'pickup_radius',
                                        'value' => $model->pickup_radius ? $model->pickup_radius . ' ' . Yii::t('app', 'km') : '',
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
