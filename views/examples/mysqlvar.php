<?php
$this->title = Yii::t('app', 'MySQL Variables');
$this->params['breadcrumbs'][] = $this->title;

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
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Variable name</th>
                            <th scope="col">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mysqlvars as $mysqlVar => $mysqlValue) : ?>
                            <tr>
                                <td><?php echo $mysqlVar; ?></td>
                                <td><?php echo $mysqlValue; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
