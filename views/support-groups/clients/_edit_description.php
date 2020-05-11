<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupportGroupCommand */

?>

<div class="card-header mb-3">
    <div class="row">
        <div class="col-12 text-right">
            <a class="btn btn-light" href="#" title="Edit" data-toggle="modal" data-target="#commonModal"><i class="fas fa-edit"></i></a>
            <?php $form = ActiveForm::begin() ?>
            <div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="commonModalTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-left">
                            <?= $form->field($model, 'description')->textarea() ?>
                        </div>
                        <div class="card-footer text-left">
                            <button type="submit" class="btn btn-success">Save</button>
                            <a class="btn btn-secondary" href="#" data-dismiss="modal">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
