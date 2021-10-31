<?php

use app\components\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use app\models\User;
use yii\helpers\Url;

/**
 * @var $this View
 * @var $usersCount int
 * @var $dataProvider ArrayDataProvider
 */

$this->title = Yii::t('app', 'Users') . ': ' . $usersCount;
?>
<div class="users">
    <div class="row">
        <div class="col-12">
            <div class="card">
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
