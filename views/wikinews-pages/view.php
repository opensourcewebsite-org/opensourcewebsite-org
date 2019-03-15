<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\WikinewsPage */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Wikinews Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="wikinews-page-view">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="col-md-12">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                [
                                    'attribute' => 'language_id',
                                    'value' => function ($model) {
                                        return $model->language->name;
                                    },
                                ],
                                'title',
                                'wikinews_page_url',
                                'created_at'
                            ],
                        ]) ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Are you sure you want to delete this item?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
