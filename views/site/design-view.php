<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('menu', 'View design');
if (!empty($moqup)) {
    ?>
    <div class="card">
        <div class="card-header d-flex p-0">
            <h3 class="card-title p-3">
                <?php echo $moqup->title; ?>
            </h3>
            <div class="ml-auto p-2">
                <button type="button" class="btn btn-primary">Follow</button>
                <button type="button" class="btn btn-primary active">Following</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <style>
    <?php
    if (!empty($css)) {
        echo $css->css;
    }
    ?>
            </style>
            <?php echo $moqup->html; ?>
        </div>
    </div>
    <?php
}
?>