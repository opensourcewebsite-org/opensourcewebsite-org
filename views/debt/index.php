<?php

use app\models\Debt;
use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;
use app\components\helpers\DebtHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Debts');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="callout callout-danger">
    <h5><?= Yii::t('app', 'Attention') ?>!</h5>
    <p>
        <?= Yii::t('app', 'This feature works in test mode') ?>. <?= Yii::t('app', 'Please help to test all functions of this') ?>. <?= Yii::t('app', 'All data of debts will be deleted from 2020-07-11 or earlier') ?>. <?= Yii::t('app', 'After that, this feature will work in an operating mode') ?>.
    </p>
</div>

<div class="debt-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-4">
                            <?= AddButton::widget([
                                'url' => ['debt/create'],
                                'options' => [
                                    'title' => 'New Debt',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-hover'],
                        'columns' => [
                            [
                                'label' => 'Currency',
                                'value' => function (Debt $data) {
                                    return $data->currency->code ?? null;
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'My Deposits',
                                'value' => function (Debt $data) {
                                    $text = DebtHelper::renderAmount($data->depositPending, $data->depositConfirmed);
                                    return Html::a($text, ['/debt/view', 'direction' => Debt::DIRECTION_DEPOSIT, 'currencyId' => $data->currency_id]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'label' => 'My Credits',
                                'value' => function (Debt $data) {
                                    $text = DebtHelper::renderAmount($data->creditPending, $data->creditConfirmed);
                                    return Html::a($text, ['/debt/view', 'direction' => Debt::DIRECTION_CREDIT, 'currencyId' => $data->currency_id]);
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
