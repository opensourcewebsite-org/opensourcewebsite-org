<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\Currency;
use app\models\Resume;
use app\models\search\ResumeSearch;
use app\widgets\Alert;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use app\components\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\widgets\buttons\AddButton;
use yii\grid\GridView;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var int $vacancyId
 */

$this->title = Yii::t('app', 'Matched Resumes');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vacancies'), 'url' =>['/vacancy/index']];
$this->params['breadcrumbs'][] = ['label' => '#' . $model->id, 'url' => ['/vacancy/view', 'id' => $model->id]];
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
                                'value' => function ($model) {
                                    return $model->name . ($model->company_id ? '<br/><i>' . $model->company->name . '</i>' : '');
                                },
                                'enableSorting' => false,
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'min_hourly_rate',
                                'value' => function ($model) {
                                    return $model->min_hourly_rate ? $model->min_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, Resume $matchModel) use ($model) {
                                        return Html::a(
                                            $matchModel->isNewMatch() ? Html::badge('info', 'new') : Html::icon('eye'),
                                            Url::to(['view-match', 'vacancyId' => $model->id, 'resumeId' => $matchModel->id]),
                                            ['class' => 'btn btn-outline-primary']
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
