
<?php

use app\models\Debt;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'My credits') . ' (' . $currency->code . ')';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts')];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My credits'), 'url' => ['debt/credit']];
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
                                'label' => Yii::t('app', 'User'),
                                'value' => function ($model) {
                                    return Html::a($model->getDisplayName(), ['contact/view-user', 'id' => $model->id]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => Yii::t('app', 'Amount'),
                                'value' => function ($model) use ($user, $currency) {
                                    return Html::a($user->getCreditDebtBalance($currency->id, $model->id) . ' ' . $currency->code, ['/debt/currency-user-credit', 'currencyId' => $currency->id, 'counterUserId' => $model->id]);
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
