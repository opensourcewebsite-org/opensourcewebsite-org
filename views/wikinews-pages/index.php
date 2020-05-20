<?php

use app\components\helpers\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\UserWikiToken;
use app\models\UserWikiPage;
use app\components\TitleColumn;
use app\widgets\ModalAjax;

/* @var $this \yii\web\View */

$this->title = Yii::t('menu', 'Wikinews pages');
?>
<div class="card">
    <div class="card-header d-flex p-0">
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <?= ModalAjax::widget([
                    'id' => 'add-wikinews',
                    'header' => Yii::t('user', 'Add wikinews page'),
                    'closeButton' => false,
                    'toggleButton' => [
                        'label' => Icon::ADD,
                        'class' => 'btn btn-outline-success',
                        'style' =>  ['float' => 'right'],
                    ],
                    'url' => Url::to(['wikinews-pages/create']),
                    'ajaxSubmit' => true,
                ]);?>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
				'pager' => [
						'hideOnSinglePage' => false,

						// Customzing options for pager container tag
						'options' => [
							'tag' => 'ul',
							'class' => 'pagination float-right',
						],

						// Customzing CSS class for pager link
						'linkOptions' => ['class' => 'page-link'],
						'linkContainerOptions' => ['class' => 'page-item'],
						'activePageCssClass' => 'active',
						'disabledPageCssClass' => 'disabled',
						'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'],
					],
                'columns' => [
                    [
                        'class' => TitleColumn::class,
                        'label' => 'Lang',
						'value' => function ($model) {
                            return $model->language->name;
                        },
                        'format' => 'text',
                    ],
                    [
                        'class' => TitleColumn::class,
                        'label' => 'Title',
                        'value' => function ($model) {
                            $link = "https://{$model->language->code}.wikinews.org/?curid=".$model->pageid;//wiki/".$model->title;
                            return Html::a(urldecode($model->title), $link, ['target' => '_blank']);
                        },
                        'format' => 'raw',
                    ]
                ],
            ]); ?>
    </div>
</div>
