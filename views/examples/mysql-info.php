<?php

use yii\web\View;

$this->title = Yii::t('app', 'MySQL Info');
$this->params['breadcrumbs'][] = $this->title;

/**
 * @var array $mysqlVars
 * @var $this View
 */
$JS = <<<JS
    "use strict"
    $(document).ready(function(){
        $(".table td:last-child").css({'word-break': 'break-all'});
    })
JS;
$this->registerJs($JS);
?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <div id="w0" class="grid-view">
                <table class="table table-condensed table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Server System Variable</th>
                            <th scope="col">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mysqlVars as $mysqlVar => $mysqlValue) : ?>
                            <tr>
                                <td><?= $mysqlVar; ?></td>
                                <td><?= $mysqlValue; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
