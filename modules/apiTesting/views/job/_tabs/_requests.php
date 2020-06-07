<?php

use app\components\helpers\Icon;
use app\modules\apiTesting\models\ApiTestJob as ApiTestJob;
use app\widgets\buttons\AddButton;
use app\widgets\ModalAjax;
use yii\grid\GridView;
use yii\helpers\Url;

/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var ApiTestJob $job
 */
?>
<?= ModalAjax::widget([
    'id' => 'requests-modal',
    'header' => Yii::t('user', 'Add request'),
    'closeButton' => false,
    'toggleButton' => [
        'label' => Icon::EDIT,
        'class' => 'btn btn-outline-success ml-4 mb-4',
        'style' => [
            'position' => 'absolute',
            'right' => '20px',
            'top' => '10px'
        ]
    ],
    'url' => Url::to(['requests-manage', 'id' => $job->id]),
    'ajaxSubmit' => true,
]);
?>
<?=$this->render('../../project/_requests_grid', [
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $job->getRequests()
    ])
]); ?>
