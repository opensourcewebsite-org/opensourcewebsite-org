<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Vacancy;
use app\widgets\ContactWidget\ContactWidget;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var $this View
 * @var $model Vacancy
 * @var int $resumeId
 */

$this->title = Yii::t('app', 'Vacancy') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' => ['/resume/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $resumeId, 'url' =>['/resume/view', 'id' => $resumeId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Vacancies'), 'url' => ['/vacancy/matches', 'resumeId' => $resumeId]];
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
                                'name',
                                'responsibilities:ntext',
                                'requirements:ntext',
                                'conditions:ntext',
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
                                    'attribute' => 'max_hourly_rate',
                                    'value' => $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞',
                                ],
                                'remote_on:boolean',
                                [
                                    'label' => Yii::t('jo', 'Offline work'),
                                    'value' => (bool)$model->location ? Yii::t('app', 'Yes') : Yii::t('app', 'No'),
                                ],
                                [
                                    'attribute' => 'location',
                                    'visible' => (bool)$model->location,
                                    'value' => function () use ($model) {
                                        return Html::a(
                                            $model->location,
                                            Url::to(['view-location', 'id' => $model->id]),
                                            ['class' => 'modal-btn-ajax']
                                        ) ;
                                    },
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'gender_id',
                                    'visible' => (bool)$model->gender_id,
                                    'value' => function () use ($model) {
                                        return $model->gender ? $model->gender->name : '';
                                    },
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($model->languages): ?>
    <div class="index">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('jo', 'Required languages') ?></h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered detail-view mb-0">
                                <tbody>
                                <?php foreach ($model->languages as $vacancyLanguage): ?>
                                    <tr>
                                        <td><?= $vacancyLanguage->getLabel() ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($model->company_id): ?>
    <div class="index">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('app', 'Company') ?></h3>
                        </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?= DetailView::widget([
                                'model' => $company = $model->company,
                                'attributes' => [
                                    'id',
                                    [
                                        'attribute' => 'name',
                                        'visible' => (bool)$company->name,
                                    ],
                                    [
                                        'attribute' => 'url',
                                        'visible' => (bool)$company->url,
                                        'format' => 'url',
                                    ],
                                    [
                                        'attribute' => 'address',
                                        'visible' => (bool)$company->address,
                                    ],
                                    [
                                        'attribute' => 'description',
                                        'visible' => (bool)$company->description,
                                        'format' => 'ntext',
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= ContactWidget::widget(['user' => $model->user])?>
