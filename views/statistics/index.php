<?php

use yii\helpers\Html;
use app\models\UserStatistic;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\web\View;
use yii\data\ArrayDataProvider;

/**
 * @var $this View
 * @var $usersCount int
 * @var $dataProvider ArrayDataProvider
 */

?>
<div class="info-box">
    <span class="info-box-icon bg-info"><i class="fa fa-users"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Registered Users</span>
        <span class="info-box-number"><?= $usersCount ?></span>
    </div>
</div>
<?php Pjax::begin([
    'id' => 'statistics',
])?>
<div class="statistics">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Age'), ['statistics/index', 'type' => UserStatistic::AGE], [
                                'class' => 'nav-link show ' .
                                (Yii::$app->request->get('type', 'age') === UserStatistic::AGE ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Year of birth'), ['statistics/index', 'type' => UserStatistic::YEAR_OF_BIRTH], [
                                'class' => 'nav-link show ' .
                                (Yii::$app->request->get('type') === UserStatistic::YEAR_OF_BIRTH ? 'active' : '')
                            ]); ?>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Gender'), ['statistics/index', 'type' => UserStatistic::GENDER], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::GENDER ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Sexuality'), ['statistics/index', 'type' => UserStatistic::SEXUALITY], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::SEXUALITY ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Currency'), ['statistics/index', 'type' => UserStatistic::CURRENCY], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::CURRENCY ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Interface Language'), ['statistics/index', 'type' => UserStatistic::INTERFACE_LANGUAGE], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::INTERFACE_LANGUAGE ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Language and Levels'), ['statistics/index', 'type' => UserStatistic::LANGUAGE_AND_LEVEL], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::LANGUAGE_AND_LEVEL ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Citizenships'), ['statistics/index', 'type' => UserStatistic::CITIZENSHIP], [
                                'class' => 'nav-link show ' .
                                    (Yii::$app->request->get('type') === UserStatistic::CITIZENSHIP ? 'active' : '')
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'id' => 'ages',
                        'dataProvider' => $dataProvider,
                        'layout' => "{items}<div class='pagination pagination-sm no-margin pull-right'>{pager}</div><div class='card-footer clearfix'></div>",
                        'tableOptions' => ['class' => 'table table-condensed table-hover'],
                        'pager' => [
                            'class' => '\yii\widgets\LinkPager',
                            'linkOptions' => [
                                'class' => 'page-link'
                            ],
                            'pageCssClass' => 'page-item',
                            'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'disabled page-link']
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end()?>
