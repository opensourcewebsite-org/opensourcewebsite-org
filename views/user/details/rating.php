<?php

use app\components\Converter;
use yii\widgets\LinkPager;

$this->title = Yii::t('app', 'Rating transactions');
?>
<div class="issue-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($models as $key => $model) : ?>
                                    <tr>
                                        <td><?= Converter::formatDate($model->created_at); ?></td>
                                        <td><?= $model->amount; ?></td>
                                        <td><?= $model->getTypeName(); ?></td>
                                    </tr>
                                <?php endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <?php echo LinkPager::widget([
                            'pagination' => $pages,
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
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
