<?php

use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;
use yii\helpers\Html;
use app\components\Converter;

$this->title = Yii::t('app', 'Currency');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Currencies'), 'url' => ['currency']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover">
            <tbody>
                <tr>
                    <th scope="col">ID</th>
                    <td><?= $model->id; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Name') ?></th>
                    <td><?= $model->name; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Code') ?></th>
                    <td><?= $model->code; ?></td>
                </tr>
                <tr>
                    <th scope="col"><?= Yii::t('app', 'Symbol') ?></th>
                    <td><?= $model->symbol; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
