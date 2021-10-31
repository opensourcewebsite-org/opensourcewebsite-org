<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\components\helpers\Html;

/**
 * @var View $this
 */

$this->title = Yii::t('app', 'Participants');
$this->params['breadcrumbs'][] = 'Stellar';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Basic income'), 'url' => ['index']];
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <div class="col-sm-12">
                        <?= $this->render('_navbar'); ?>
                    </div>
                </div>
                <div class="card-body">
                </div>
            </div>
        </div>
    </div>
</div>
