<?php

use app\components\helpers\ArrayHelper;

?>
<div class="row">
    <div class="col-md-2">
        <?= $form->field($model, 'method')->dropDownList($model::getMethodsList(), ['prompt' => 'Methods'])->label(false); ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'server_id')->dropDownList(
            ArrayHelper::map($project->servers, 'id', 'fullAddress'),
            ['prompt' => 'Servers']
        )->label(false); ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'uri')->textInput(['maxlength' => true, 'placeholder' => 'URI'])->label(false); ?>
    </div>
</div>
