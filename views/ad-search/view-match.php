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

$this->title = Yii::t('app', 'Search') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Offers'), 'url' => ['/ad-offer/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $adOfferId, 'url' => ['/ad-offer/view', 'id' => $adOfferId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Searches'), 'url' => ['/ad-search/show-matches', 'adOfferId' => $adOfferId]];
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
                                    [
                                        'attribute' => 'sectionName',
                                        'label' => Yii::t('app', 'Section'),
                                        'value' => function ($model) {
                                            return $model->sectionName;
                                        },
                                    ],
                                    'title',
                                    'description:ntext',
                                    [
                                        'label' => Yii::t('app', 'Keywords'),
                                        'visible' => (bool)$model->keywords,
                                        'value' => function () use ($model) {
                                            $text = '';

                                            foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                                $text .= Html::tag('span', $keyword, ['class' => 'badge badge-primary']) . '&nbsp';
                                            }

                                            return $text;
                                        },
                                        'format' => 'raw',
                                    ],
                                    [
                                        'attribute' => 'max_price',
                                        'value' => $model->max_price ? $model->max_price . ' ' . $model->currency->code : '∞',
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
