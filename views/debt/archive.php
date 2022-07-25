<?php

use app\models\Debt;
use app\widgets\buttons\AddButton;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Archive');
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <?= $this->render('_card-header', $_params_); ?>
                <div class="card-body p-0">
                    TBD
                </div>
            </div>
        </div>
    </div>
</div>
