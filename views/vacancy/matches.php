<?php

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\search\VacancySearch;
use app\models\Vacancy;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var int $resumeId
 */

$this->title = Yii::t('app', "Matched Vacancies");
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Resumes'), 'url' =>['/resume/index']];
$this->params['breadcrumbs'][] = ['label' => "#{$resumeId}", 'url' =>['/resume/view', 'id' => $resumeId]];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="vacancy-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'name',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'max_hourly_rate',
                                'value' => function($model) {
                                    return $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) use ($resumeId) {
                                        $url = Url::to(['view-match', 'vacancyId' => $model->id, 'resumeId' => $resumeId]);
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
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
