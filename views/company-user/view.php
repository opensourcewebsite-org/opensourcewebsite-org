<?php
declare(strict_types=1);


use app\models\Company;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/* @var View $this */
/* @var Company $model */

$this->title = Yii::t('app', 'Company') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;
?>
<div class="index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex p-0">
                    <ul class="nav nav-pills ml-auto p-2">
                        <li class="nav-item align-self-center mr-3">
                            <?= EditButton::widget([
                                'url' => ['update', 'id' => $model->id],
                                'options' => [
                                    'title' => 'Edit Company',
                                ]
                            ]); ?>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'id',
                                [
                                    'attribute' => 'name',
                                    'visible' => (bool)$model->name,
                                ],
                                [
                                    'attribute' => 'url',
                                    'visible' => (bool)$model->url,
                                    'format' => 'url',
                                ],
                                [
                                    'attribute' => 'address',
                                    'visible' => (bool)$model->address,
                                ],
                                [
                                    'attribute' => 'description',
                                    'visible' => (bool)$model->description,
                                    'format' => 'ntext',
                                ],
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
