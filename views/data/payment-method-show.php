<?php

use yii\widgets\LinkPager;

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Payment Method - <?= $paymentMethod ?></h3>
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
