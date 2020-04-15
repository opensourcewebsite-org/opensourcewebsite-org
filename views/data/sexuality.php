<?php

use yii\widgets\LinkPager;

?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sexuality</h3>
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
        <table class="table">
            <tbody>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                </tr>
                <?php foreach ($models as $key => $model) : ?>
                    <tr>
                        <td><?= $model->id ?></td>
                        <td><?php echo $model->name ?? null; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
