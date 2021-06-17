<?php

use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;
use yii\helpers\Html;
use app\components\Converter;

$this->title = Yii::t('app', 'Currency');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currencies'), 'url' => ['currency']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th scope="col">ID</th>
                    <td><?= $model->id; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Name') ?></th>
                    <td><?= $model->name; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Code') ?></th>
                    <td><?= $model->code; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Symbol') ?></th>
                    <td><?= $model->symbol; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php if ($currencyRates) : ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Currency') ?> Rates</h3>
        <div class="card-tools">
            <?= LinkPager::widget([
                'pagination' => $pages,
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
            ]); ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Currency</th>
                    <th scope="col">Rate</th>
                    <th scope="col">Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currencyRates as $currencyRate) : ?>
                    <tr>
                        <td><?= Html::a("{$currencyRate->toCurrency->name} ({$currencyRate->toCurrency->code})", ['data/currency/' . $currencyRate->toCurrency->id]); ?></td>
                        <td><?= $currencyRate->rate; ?></td>
                        <td><?= Converter::formatDate($currencyRate->updated_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
