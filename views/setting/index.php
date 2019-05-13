<?php
use yii\helpers\Html;
use app\components\Converter;
use yii\widgets\LinkPager;

$this->title = Yii::t('app', 'Website settings');
$this->params['breadcrumbs'][] = $this->title;
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
                                        <th>Name</th>
                                        <th>Value</th>
                                        <th>Last update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($models as $key => $model): ?>
                                        <tr>
                                            <td>
                                            <?= Html::a($model->key, ['/setting/values', 'id' => $model->id]); 
                                            ?>
                                            </td>
                                            <td><?php echo $model->value ?? null; ?></td>
                                            <td><?php echo Converter::formatDate($model->updated_at); ?></td>
                                        </tr>
                                    <?php endforeach;?>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <?php echo LinkPager::widget([
                            'pagination' => $pages,
                            'hideOnSinglePage' => false,
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
