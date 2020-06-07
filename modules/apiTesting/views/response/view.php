<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\apiTesting\models\ApiTestResponse */

$this->title = $model->request->name.' response '.$model->id.' test results';
$this->params['breadcrumbs'][] = ['label' => 'Api Test Responses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="api-test-response-view">
    <div class="card">
        <div class="card-body">
            <?= $this->render('../common/_test_result_column', [
                'response' => $model,
            ]); ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <?= \yii\bootstrap4\Tabs::widget([
                'items' => [
                    [
                        'label' => 'Headers',
                        'content' => Html::tag(
                                'div',
                                $model->headers,
                                [
                                    'contenteditable' => true,
                                    'class' => 'p-2 mt-2',
                                    'style' => [
                                        'overflow' => 'auto',
                                        'border' => '1px solid #e3e3e3',
                                        'height' => '200px'
                                    ]
                                ]
                        ),
                    ],
                    [
                        'label' => 'Body',
                        'content' => Html::tag('div', $model->body,
                            [
                                'contenteditable' => true,
                                'class' => 'p-2 mt-2',
                                'style' => [
                                    'overflow' => 'auto',
                                    'border' => '1px solid #e3e3e3',
                                    'height' => '200px'
                                ]
                            ]
                        ),
                    ],
                    [
                        'label' => 'Cookies',
                        'content' => Html::tag('div', $model->cookies,
                            [
                                'overflow' => 'auto',
                                'contenteditable' => true,
                                'class' => 'p-2 mt-2',
                                'style' => [
                                    'border' => '1px solid #e3e3e3',
                                    'height' => '200px'
                                ]
                            ]
                        ),
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
