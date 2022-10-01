<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\components\helpers\Html;
use app\models\Currency;
use app\models\Resume;
use app\models\search\ResumeSearch;
use app\widgets\Alert;
use app\widgets\buttons\AddButton;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var ResumeSearch $searchModel
 */

$this->title = Yii::t('app', 'Resumes');
$this->params['breadcrumbs'][] = $this->title;

$displayActiveTab = $searchModel->status === ResumeSearch::STATUS_ON;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card table-overflow">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-6">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item">
                                <?= Html::a(
    Yii::t('app', 'Active'),
    ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_ON],
    ['class' => 'nav-link show ' . ($displayActiveTab ? 'active' : '')]
); ?>
                            </li>
                            <li class="nav-item">
                                <?= Html::a(
                                    Yii::t('app', 'Inactive'),
                                    ['/resume/index', 'ResumeSearch[status]' => ResumeSearch::STATUS_OFF],
                                    ['class' => 'nav-link show ' . (!$displayActiveTab ? 'active' : '')]
                                ); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <div class="right-buttons float-right">
                            <?= AddButton::widget([
                                'url' => ['create'],
                                'options' => [
                                    'title' => 'New Resume',
                                    'style' => [
                                        'float' => 'right',
                                    ],
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
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
                                'attribute' => 'min_hourly_rate',
                                'value' => function ($model) {
                                    return $model->min_hourly_rate ? $model->min_hourly_rate . ' ' . $model->currency->code : '∞';
                                },
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Offers'),
                                'value' => function (Resume $model) {
                                    return ($matchesCount = $model->getMatches()->count()) ?
                                        Html::a(
                                            $model->getNewMatches()->exists() ? Html::badge('info', 'new') : $matchesCount,
                                            Url::to(['/vacancy/matches', 'resumeId' => $model->id]),
                                        ) : '';
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                                'visible' => $displayActiveTab,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        return Html::a(Html::icon('eye'), $url, ['class' => 'btn btn-outline-primary float-right']);
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
