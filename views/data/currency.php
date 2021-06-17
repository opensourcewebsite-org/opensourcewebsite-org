<?php

use yii\widgets\LinkPager;
use yii\helpers\Html;

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Currencies') ?></h3>
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
                    <th scope="col"><?= Yii::t('app', 'Name') ?></th>
                    <th scope="col"><?= Yii::t('app', 'Code') ?></th>
                    <th scope="col"><?= Yii::t('app', 'Symbol') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $key => $model) : ?>
                    <tr>
                        <td><?= $model->id ?></td>
                        <td><?= Html::a($model->name, ['data/currency/' . $model->id]); ?></td>
                        <td><?= $model->code ?? null; ?></td>
                        <td><?= $model->symbol ?? null; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
