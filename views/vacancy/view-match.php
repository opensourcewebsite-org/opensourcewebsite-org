<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Vacancy;
use app\widgets\ContactWidget\ContactWidget;
use yii\helpers\Html;
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
$this->params['breadcrumbs'][] = ['label' => "#" . $resumeId, 'url' =>['/resume/view', 'id' => $resumeId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Vacancies'), 'url' => ['/vacancy/show-matches', 'resumeId' => $resumeId]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>

    <div class="vacancy-view">
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
                                    'requirements:ntext',
                                    'conditions:ntext',
                                    'responsibilities:ntext',
                                    [
                                        'label' => Yii::t('app', 'Keywords'),
                                        'visible' => (bool)$model->keywords,
                                        'value' => function() use ($model) {
                                            $text = '';

                                            foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                                $text .= '<small class="badge badge-primary">' . $keyword . '</small>&nbsp';
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
                                        'label' => Yii::t('app', 'Offline work'),
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
                                        'value' => function() use ($model) {
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

<?php if ($model->company_id): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Company') ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= DetailView::widget([
                            'model' => $model->company,
                            'attributes' => [
                                'id',
                                'name',
                                'url:url',
                                'address',
                                'description:ntext',
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($model->languagesWithLevels): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('app', 'Languages') ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered detail-view mb-0">
                            <tbody>
                            <?php foreach($model->languagesWithLevels as $languagesWithLevel): ?>
                                <tr>
                                    <td>
                                        <strong><?= $languagesWithLevel->language->name ?></strong>
                                    </td>
                                    <td>
                                        <?= $languagesWithLevel->level->description ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= ContactWidget::widget(['user' => $model->user])?>
