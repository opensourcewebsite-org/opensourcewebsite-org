<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\components\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use app\models\User;
use yii\helpers\Url;

/**
 * @var View $this
 */

$this->title = Yii::t('app', 'Participants')  . ': ' . $usersCount;
$this->params['breadcrumbs'][] = 'Stellar';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Basic income'), 'url' => ['index']];
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-12">
                        <?= $this->render('_navbar'); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'id' => 'users',
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => Yii::t('app', 'Rank'),
                                'content' => function (User $model) {
                                    return $model->getRank();
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'User'),
                                'content' => function (User $model) {
                                    return Html::a($model->getDisplayName(), ['contact/view-user', 'id' => $model->id]);
                                },
                                'format' => 'html',
                                'enableSorting' => false,
                            ],
                            [
                                'label' => Yii::t('app', 'Positive votes'),
                                'content' => function (User $model) {
                                    return ($model->getBasicIncomePositiveVotesCount() ?
                                        Html::a($model->getBasicIncomePositiveVotesCount(), Url::to(['/stellar-basic-income/user-view', 'userId' => $model->id]))
                                        : '0')
                                        . ($model->getBasicIncomeVoteByUserId() ? ' ' . ($model->getBasicIncomeVoteByUserId() == 1 ? Html::badge('success', Yii::t('app', 'Your positive vote')) : Html::badge('danger', Yii::t('app', 'Your negative vote'))) : '');
                                },
                                'format' => 'raw',
                                'enableSorting' => false,
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
