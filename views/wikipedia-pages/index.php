<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\models\UserWikiToken;
use app\components\TitleColumn;

/* @var $this \yii\web\View */

$this->title = Yii::t('menu', 'Wikipedia watchlists');
$countTokens = $tokensDataProvider->count;

?>
<div id="flash-message" style="display: none;" class="alert alert-success"></div>
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
            <?php Pjax::begin([
                'id' => 'watchlist-grid',
            ]); ?>
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
                            $count = $model->getWikiPagesIds()->count();
                            if ($count > 0) {
                                return Html::a($count, ['wikipedia-page/view/' . $model->language->code]);
                            }
                        },
                        'format' => 'raw',
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
            <?php Pjax::end(); ?>
        <?php endif ?>
    </div>
</div>
<script src="https://js.pusher.com/4.3/pusher.min.js"></script>
<script>
  var pusher = new Pusher("<?php echo getenv('PUSHER_APP_KEY'); ?>", {
    cluster: 'ap2',
    forceTLS: true
  });

  var channel = pusher.subscribe('my-channel');
  channel.bind('my-event', function(data) {
      $.pjax.reload({container: '#watchlist-grid', timeout: 4000});
      $('#flash-message').html(data.message).fadeIn().delay(8000).fadeOut();
  });
</script>