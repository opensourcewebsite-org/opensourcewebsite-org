<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Countries</h3>
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
                    <th scope="col">Code</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Wikipedia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $key => $model) : ?>
                    <tr>
                        <td><?= $model->id ?></td>
                        <td><?= $model->name ?? null; ?></td>
                        <td><?= $model->code ?? null; ?></td>
                        <td><?= $model->slug ?? null; ?></td>
                        <td><?= Html::a($model->wikipedia, $model->wikipedia); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
