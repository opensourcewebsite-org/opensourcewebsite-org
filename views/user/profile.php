<?php

use app\models\Contact;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
$this->title = Yii::t('app', 'Account');
?>

<div class="user-profile-form">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                <?php
                    echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'label' => 'Username',
                                'value' => $model->username,
                                'visible' => (bool)$model->username
                            ],
                            [
                                'attribute' => 'Real Confirmations',
                                'value' => $realConfirmations
                            ],
                        ],
                        'options' => ['class' => 'table table-hover detail-view']
                    ]);
                ?>
                </div>
            </div>
        </div>
    </div>
</div>
