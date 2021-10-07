<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\AdOffer;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;
use app\widgets\ContactWidget\ContactWidget;

/**
 * @var View $this
 * @var AdOffer $model
 * @var int $adSearchId
 */

$this->title = Yii::t('app', 'Offer') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Searches'), 'url' => ['/ad-search/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $adSearchId, 'url' => ['/ad-search/view', 'id' => $adSearchId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Offers'), 'url' => ['/ad-offer/show-matches', 'adSearchId' => $adSearchId]];
$this->params['breadcrumbs'][] = '#' . $model->id;
?>
<div class="index">
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
                                    'attribute' => 'price',
                                    'value' => $model->price ? $model->price . ' ' . $model->currency->code : '∞',
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= ContactWidget::widget(['user' => $model->user])?>
