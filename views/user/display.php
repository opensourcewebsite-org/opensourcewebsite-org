<?php
use yii\helpers\Html;
use app\models\UserStatistic;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\base\View;
use yii\data\ArrayDataProvider;

/**
 * @var $this View
 * @var $confirmedUsersCount int
 * @var $dataProvider ArrayDataProvider
 */
?>
<div class="info-box">
    <span class="info-box-icon bg-info"><i class="fa fa-users"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Registered Users</span>
        <span class="info-box-number"><?php echo $confirmedUsersCount; ?></span>
    </div>
</div>

<div class="user-statistics">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Age'), ['user/display', 'type' => UserStatistic::AGE], [
                                'class' => 'nav-link show ' .
                                (Yii::$app->request->get('type', 'age') === UserStatistic::AGE ? 'active' : '')
                            ]); ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a(Yii::t('app', 'Year of birth'), ['user/display', 'type' => UserStatistic::YEAR_OF_BIRTH], [
                                'class' => 'nav-link show ' .
                                (Yii::$app->request->get('type') === UserStatistic::YEAR_OF_BIRTH ? 'active' : '')
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?php Pjax::begin([
                        'id' => 'statistic'
                    ]);
                    echo GridView::widget([
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
                        ]
                    ]);
                    Pjax::end();
                    ?>

                </div>

            </div>
        </div>
    </div>
</div>
