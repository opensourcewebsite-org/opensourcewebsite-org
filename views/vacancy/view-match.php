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

$this->title = Yii::t('app', 'Matched Vacancy') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' => ['/resume/index']];
$this->params['breadcrumbs'][] = ['label' => "#{$resumeId}", 'url' =>['/resume/view', 'id' => $resumeId]];
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
                                        'value' => function() use ($model) {
                                            return implode(',', ArrayHelper::getColumn($model->keywords, 'keyword'));
                                        }
                                    ],
                                    'max_hourly_rate:decimal',
                                    [
                                        'attribute' => 'currency_id',
                                        'value' => $model->currency_id ? $model->currency->code . ' - ' . $model->currency->name : '',
                                    ],
                                    'remote_on:boolean',
                                    [
                                        'attribute' => 'location',
                                        'visible' => !$model->isRemote(),
                                        'value' => function () use ($model) {
                                            return Html::a(
                                                $model->location,
                                                Url::to(['view-location', 'id' => $model->id]),
                                                ['class' => 'modal-btn-ajax']
                                            ) ;
                                        },
                                        'format' => 'raw'
                                    ],
                                    [
                                        'attribute' => 'company_id',
                                        'value' => function() use ($model) {
                                            return $model->company ? $model->company->name : '';
                                        }
                                    ],
                                    [
                                        'attribute' => 'gender_id',
                                        'value' => function() use ($model) {
                                            return $model->gender ? $model->gender->name : '';
                                        }
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

<?= ContactWidget::widget(['user' => $model->globalUser])?>
