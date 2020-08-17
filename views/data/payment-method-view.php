<?php

use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Payment Method');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Payment methods'), 'url' => ['payment-method']];
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
                    <th scope="col">Name</th>
                    <td><?= $model->name; ?></td>
                </tr>
                <tr>
                    <th scope="col">Type</th>
                    <td><?= $model->getTypeName(); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php if ($currencies) : ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Currencies</h3>
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
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Type</th>
                    <th scope="col">Symbol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currencies as $currency) : ?>
                    <tr>
                        <td><?= $currency->id; ?></td>
                        <td><?= Html::a($currency->name, ['data/currency/' . $currency->id]); ?></td>
                        <td><?= $currency->code; ?></td>
                        <td><?= $currency->symbol; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
