<?php

use app\models\Debt;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Currency;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */

$currency = Currency::findOne($currencyId);
$this->title = Yii::t('app', $currency->code);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Debts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $currencyId;

?>
<div class="debt-view">
    <div class="card">
        <div class="card-header d-flex p-0">
            <ul class="nav nav-pills ml-auto p-2">
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'My Deposits'), ['debt/view', 'direction' => Debt::DIRECTION_DEPOSIT, 'currencyId' => $currencyId], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_DEPOSIT ? 'active' : ''),
                    ]); ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(Yii::t('app', 'My Credits'), ['debt/view', 'direction' => Debt::DIRECTION_CREDIT, 'currencyId' => $currencyId], [
                        'class' => 'nav-link show ' . ((int) $direction === Debt::DIRECTION_CREDIT ? 'active' : ''),
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
                        'label' => 'User',
                        'value' => function ($data) use ($direction, $currencyId) {
                            if ($direction == Debt::DIRECTION_DEPOSIT) {
                                $userId = $data->fromUser->id;
                            } elseif ($direction == Debt::DIRECTION_CREDIT) {
                                $userId = $data->toUser->id;
                            }

                            return Html::a($data->getUserDisplayName($direction), [
                                'view-user', 
                                'direction'   => $direction, 
                                'userId'      => $userId, 
                                'currencyId'  => $currencyId
                            ]);
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
