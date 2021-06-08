<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\AdOffer;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/**
 * @var View $this
 * @var AdOffer $model
 * @var int $adSearchId
 */

$this->title = Yii::t('app', 'Ad Offer') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Ad Searches'), 'url' => ['/ad-search/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $adSearchId, 'url' => ['/ad-search/view', 'id' => $adSearchId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Ad Offers'), 'url' => ['/ad-offer/show-matches', 'adSearchId' => $adSearchId]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
    <div class="ad-offer-view">
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
                                        'attribute' => 'price',
                                        'value' => $model->price ? $model->price . ' ' . $model->currency->code : '',
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
                                        'attribute' => 'delivery_radius',
                                        'value' => $model->delivery_radius ? $model->delivery_radius . ' ' . Yii::t('app', 'km') : '',
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

