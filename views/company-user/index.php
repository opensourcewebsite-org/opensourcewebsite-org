<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\CompanyUser;
use app\models\Currency;
use app\models\Resume;
use app\models\search\ResumeSearch;
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
 * @var ResumeSearch $searchModel
 */

$this->title = Yii::t('app', 'Your Companies');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="currency-exchange-order-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['company-user/create'],
                                'options' => [
                                    'title' => 'New Company',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            'company.id',
                            'company.name',
                            'company.url:url',
                            'company.address',
                            [
                                'label' => Yii::t('app','Vacancies'),
                                'content' => function (CompanyUser $model) {
                                    if ( ($vacanciesNum = $model->company->getVacancies()->count()) > 0) {
                                        return Html::a(
                                            (string)$vacanciesNum,
                                            Url::to([
                                                '/vacancy/index',
                                                'VacancySearch[company_id]' => (string)$model->company_id
                                            ])
                                        );
                                    }
                                }
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);
                                        return Html::a($icon, $url, ['class' => 'btn btn-outline-primary mx-1']);
                                    },
                                ]
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

