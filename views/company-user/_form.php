<?php

use app\models\Company;
use app\widgets\buttons\DeleteButton;
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
<div class="resume-form">
    <?php $form = ActiveForm::begin(['id' => 'create-company-form']) ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
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
                <div class="card-footer">
                    <?= SaveButton::widget(); ?>
                    <?= CancelButton::widget(['url' => '/company']); ?>
                    <?php if (!$companyUserModel->isNewRecord): ?>
                        <?= DeleteButton::widget([
                            'url' => ['delete', 'id' => $companyUserModel->id],
                            'options' => [
                                'data' => [
                                    'confirm' => Yii::t('app', 'Are you sure you want to delete this Company?'),
                                    'method' => 'post'
                                ]
                            ]
                        ]); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end() ?>
</div>


