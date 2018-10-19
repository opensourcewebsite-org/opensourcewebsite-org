<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\UserWikiToken;
use app\models\UserWikiPage;
use app\components\TitleColumn;
/* @var $this \yii\web\View */

$this->title = Yii::t('menu', 'Wikipedia watchlists');
$countTokens = $tokensDataProvider->count;

?>
<div class="card">
    <div class="card-header d-flex p-0">
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item align-self-center mr-4">
                <?= Html::button('<i class="fa fa-plus"></i>', [
                    'class' => 'btn btn-outline-success',
                    'title' => 'Add Wikipedia domains that you use',
                    'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['wiki-tokens/create']) . '", {}, function (result){
                    $("#main-modal-body").html(result);
                    $("#main-modal-header").html("' . Yii::t('app', 'Setup your connection to wikipedia.org') . '").data("target", "' . Yii::$app->urlManager->createUrl(['wiki-tokens/create']) . '");
                    $("#main-modal").modal("show");
                })',
                ]); ?>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <?php if ($countTokens): ?>
            <?= GridView::widget([
                'dataProvider' => $tokensDataProvider,
                'summary' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'class' => TitleColumn::class,
                        'title' => 'List of Wikipedia domains that you use',
                        'label' => 'Wikipedia languages',
                        'attribute' => 'name',
                        'format' => 'text',
                    ],
                    [
                        'class' => TitleColumn::class,
                        'title' => 'Links to Wikipedia watchlists on Wikipedia with Wikipedia pages that you watch.',
                        'label' => 'Watchlist links',
                        'value' => function ($model) {
                            $link = "https://{$model->language->code}.wikipedia.org/wiki/Special:Watchlist";

                            return Html::a('Watchlist', $link, ['target' => '_blank']);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'class' => TitleColumn::class,
                        'title' => 'List of titles of Wikipedia pages that you watch.',
                        'label' => 'Your pages',
                        'value' => function ($model) use ($countTokens) {
                            $response = '';
                            $count = count($model->wikiPagesIds);

                            if ($count > 0) {
                                $response .= Html::a($count, ['wikipedia-page/view/' . $model->language->code]);
                            }
                            
                            $cantMissing = $model->countMissingPages;

                            if ($cantMissing > 0) {
                                $spanMissing = Html::tag('span', $cantMissing, [
                                    'class' => 'fa badge label label-warning offset-5',
                                    'title' => 'Missing pages',
                                ]);
                                $response .= Html::a($spanMissing, [
                                    'wikipedia-pages/missing', 
                                    'userId' => $model->user_id, 
                                    'languageId' => $model->language_id
                                ]);
                            }

                            return $response;
                        },
                        'format' => 'raw',
                    ],
                    [
                        'label' => 'All users pages',
                        'headerOptions' => [
                            'title' => 'List of titles of Wikipedia pages that our users watch.',
                        ],
                        'format' => 'html',
                        'value' => function ($model) {
                            $count = UserWikiPage::find()->select('{{%wiki_page}}.id')->distinct()->joinWith('wikiPage')->where(['language_id' => $model->language->id])->count();
                            if ($count > 0) {
                                return Html::a($count, Url::to(['wikipedia-pages/view', 'code' => $model->language->code, 'all' => true]));
                            }
                        }
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{update}',
                        'controller' => 'wiki-tokens',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                if ($model->wiki_username && $model->token && $model->status == UserWikiToken::STATUS_OK) {
                                    $class = "fa fa-plug";
                                    $title = 'Wikipedia is connected';
                                } else {
                                    $class = "fa fa-plug text-danger";
                                    $title = Yii::t('app', 'Wikipedia not connected, please setup your connection to Wikipedia.');
                                }
                                $icon = Html::tag('span', '', ['class' => $class, 'data-toggle' => 'tooltip', 'title' => $title]);

                                return Html::a($icon, '#', [
                                        'onclick' => '$.get("' . Yii::$app->urlManager->createUrl(['wiki-tokens/update', 'id' => $model->id]) . '", {}, function (result){
                                    $("#main-modal-body").html(result);
                                    $("#main-modal-header").html("' . Yii::t('app', 'Setup your connection to ' . $model->language->code . '.wikipedia.org') . '").data("target", "' . Yii::$app->urlManager->createUrl(['wiki-tokens/update', 'id' => $model->id]) . '");
                                    $("#main-modal").modal("show");
                                })',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        <?php endif ?>
    </div>
</div>