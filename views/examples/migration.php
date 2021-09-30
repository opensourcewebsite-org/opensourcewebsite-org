<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Migrations') ?></h3>
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
                    <th scope="col"><?= Yii::t('app', 'Name') ?></th>
                    <th scope="col"><?= Yii::t('app', 'Applied At') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $model) : ?>
                    <tr>
                        <td><?= $model['version'] ?></td>
                        <td><?= Yii::$app->formatter->asRelativeTime($model['apply_time']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
