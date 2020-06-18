<?php

use app\components\Converter;
use app\models\SupportGroupOutsideMessage;
use yii\bootstrap4\Dropdown;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rating history</h3>


    </div>
    <div class="card-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'pager'        => [
                'options'                       => [
                    'class' => 'pagination ml-4',
                ],
                'linkContainerOptions'          => [
                    'class' => 'page-item',
                ],
                'linkOptions'                   => [
                    'class' => 'page-link',
                ],
                'disabledListItemSubTagOptions' => [
                    'tag' => 'a', 'class' => 'page-link',
                ],
            ],
            'columns' => [
                [
                    'label'=>'Created at',
                    'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                    'value' => function ($data) {
                        return Converter::formatDate($data->created_at) ?? null; // $data['name'] for array data, e.g. using SqlDataProvider.
                    },
                ],
                [
                    'label'=>'Amount',
                    'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                    'value' => function ($data) {
                        return $data->amount ?? null; // $data['name'] for array data, e.g. using SqlDataProvider.
                    },
                ],
                [
                    'label'=>'Amount' ,
                    'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
                    'value' => function ($data) {
                        return \app\models\Rating::getRatingTypeName($data->type) ?? null; // $data['name'] for array data, e.g. using SqlDataProvider.
                    },
                ],
            ],
        ]);
        ?>
    </div>

</div>
