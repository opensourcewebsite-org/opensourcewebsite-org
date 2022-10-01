<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\components\helpers\Html;
use app\models\Currency;
use app\models\search\VacancySearch;
use app\models\Vacancy;
use app\widgets\buttons\AddButton;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var int $resumeId
 */

$this->title = Yii::t('app', "Matched Vacancies");
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' =>['/resume/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['/resume/view', 'id' => $model->id]];
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
                                'attribute' => 'name',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'max_hourly_rate',
                                'value' => function ($model) {
                                    return $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, Vacancy $matchModel) use ($model) {
                                        return Html::a(
                                            $matchModel->isNewMatch() ? Html::badge('info', 'new') : Html::icon('eye'),
                                            Url::to(['view-match', 'resumeId' => $model->id, 'vacancyId' => $matchModel->id]),
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
