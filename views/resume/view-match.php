<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Resume;
use app\widgets\ContactWidget\ContactWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/**
 * @var View $this
 * @var Resume $model
 * @var int $vacancyId
 */

$this->title = Yii::t('app', 'Resume') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vacancies'), 'url' => ['/vacancies/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $vacancyId, 'url' => ['/vacancy/view', 'id' => $vacancyId]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Matched Resumes'), 'url' => ['/resume/show-matches', 'vacancyId' => $vacancyId]];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>

<div class="resume-view">
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
                                'skills:ntext',
                                'experiences:ntext',
                                'expectations:ntext',
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
                                    'attribute' => 'min_hourly_rate',
                                    'value' => $model->min_hourly_rate ? $model->min_hourly_rate . ' ' . $model->currency->code : '∞',
                                ],
                                'remote_on:boolean',
                                [
                                    'label' => Yii::t('app', 'Offline work'),
                                    'value' => (bool)$model->location ? Yii::t('app', 'Yes') : Yii::t('app', 'No'),
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
