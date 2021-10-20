
<?php

use app\models\Debt;
use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'My deposits');
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <?= $this->render('_card-header', $_params_); ?>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => Yii::t('app', 'Currency'),
                                'value' => function ($model) {
                                    return $model->code;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Amount'),
                                'value' => function ($model) use ($user) {
                                    return Html::a($user->getDepositDebtBalance($model->id), ['/debt/currency-deposit', 'currencyId' => $model->id]);
                                },
                                'format' => 'html',
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
