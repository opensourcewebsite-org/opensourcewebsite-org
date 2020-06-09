<?php

use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;

$this->params['breadcrumbs'][] = [ 'template' => "/{link}/", 'label' => 'payment method', 'url'=> ['/data/payment-method']];
$this->params['breadcrumbs'][] = 'payment method show';
$this->title = 'Payment Method';

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Payment Method</h3>
        <div class="card-tools">
            <?php echo LinkPager::widget([
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
            <tbody>
                    <tr>
                        <th scope="col">ID</th>
                        <td><?php echo $paymentMethod->id ?></td>
                    </tr>
                    <tr>
                        <th scope="col">Name</th>
                        <td><?php echo $paymentMethod->name; ?></td>
                    </tr>
                    <tr>
                        <th scope="col">Type</th>
                        <td><?php echo $paymentMethod->getTypeName(); ?></td>
                    </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Cyrrencies</h3>
        <div class="card-tools">
            <?php echo LinkPager::widget([
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
                <?php foreach ($models as $key => $model) : ?>
                    <tr>
                        <td><?php echo $model->id ?></td>
                        <td><?php echo $model->name ?? null; ?></td>
                        <td><?php echo $model->code ?? null; ?></td>
                        <td><?php echo $model->symbol ?? null; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
