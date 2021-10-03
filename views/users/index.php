
<?php
use yii\helpers\Html;
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
                                'enableSorting' => false,
                                'format' => 'raw',
                                'content' => function (User $model) {
                                    return $model->getRank();
                                }
                            ],
                            [
                                'attribute' => 'id',
                                'enableSorting' => false,
                            ],
                            [
                                'attribute' => 'username',
                                'enableSorting' => false,
                            ],
                            [
                                'class' => ActionColumn::class,
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        $icon = Html::tag('span', '', ['class' => 'fa fa-eye', 'data-toggle' => 'tooltip', 'title' => 'view']);

                                        return Html::a(
                                            $icon,
                                            Url::toRoute(['contact/view-user', 'id' => $model->id]),
                                            ['class' => 'btn btn-outline-primary mx-1']
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
