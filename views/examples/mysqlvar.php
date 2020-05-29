<?php
use app\assets\MySQLVarAsset;

$this->registerAssetBundle(MySQLVarAsset::class);

$this->title = Yii::t('app', 'MySQL Variables');
$this->params['breadcrumbs'][] = $this->title;

$JS = <<<JS
    "use strict"
    $(document).ready(function(){
        $(".table th, .table td").css({'word-break': 'break-all'});
    })
JS;
$this->registerJs($JS);
?>

<div class="issue-index">
    <div class="">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-5">Variable name</th>
                                        <th class="col-7">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mysqlvars as $mysqlVar => $mysqlValue) : ?>
                                        <tr>
                                            <td class="col-5"><?php echo $mysqlVar; ?></td>
                                            <td class="col-7"><?php echo $mysqlValue; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
