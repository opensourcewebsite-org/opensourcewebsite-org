<?php

use app\models\Company;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\models\CompanyUser;
use app\widgets\buttons\SaveButton;
use app\widgets\buttons\CancelButton;

/**
 * @var View $this
 * @var Company $companyModel
 * @var CompanyUser $companyUserModel
 */

?>
<div class="modal-header">
    <h4 class="modal-title"><?= $this->title ?></h4>
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<?php $form = ActiveForm::begin() ?>
<div class="modal-body">
    <div class="row">
        <div class="col">
            <?= $form->field($companyModel, 'name')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <?= $form->field($companyModel, 'url')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <?= $form->field($companyModel, 'address')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <?= $form->field($companyModel, 'description')->textarea() ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= SaveButton::widget(); ?>
    <?= CancelButton::widget(['options' => ['data-dismiss'=> "modal"]]); ?>
</div>
<?php ActiveForm::end() ?>
