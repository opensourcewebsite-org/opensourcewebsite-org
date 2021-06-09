<?php
declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\models\AdSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;
use app\widgets\buttons\EditButton;

/**
 * @var View $this
 * @var AdSearch $model
 */

$this->title = Yii::t('app', 'Search') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Searches'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

?>
    <div class="ad-search-view">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex p-0">
                        <ul class="nav nav-pills ml-auto p-2">
                            <li class="nav-item align-self-center mr-3">
                                <div class="input-group-prepend">
                                    <div class="dropdown">
                                        <a class="btn <?= $model->isActive() ? 'btn-primary' : 'btn-default' ?> dropdown-toggle"
                                           href="#" role="button"
                                           id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                           aria-expanded="false">
                                            <?= $model->isActive() ?
                                                Yii::t('app', 'Active') :
                                                Yii::t('app', 'Inactive') ?>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <h6 class="dropdown-header"><?= $model->getAttributeLabel('Status') ?></h6>

                                            <a class="dropdown-item status-update <?= $model->isActive() ? 'active' : '' ?>"
                                               href="#"
                                               data-value="<?= AdSearch::STATUS_ON ?>">
                                                <?= Yii::t('app', 'Active') ?>
                                            </a>

                                            <a class="dropdown-item status-update <?= $model->isActive() ? '' : 'active' ?>"
                                               href="#"
                                               data-value="<?= AdSearch::STATUS_OFF ?>">
                                                <?= Yii::t('app', 'Inactive') ?>
                                            </a>
                                        </div>
                                    </div>
                            </li>
                            <li class="nav-item align-self-center mr-3">
                                <?= EditButton::widget([
                                    'url' => ['ad-search/update', 'id' => $model->id],
                                    'options' => [
                                        'title' => 'Edit Ad Search',
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
                                        'attribute' => 'sectionName',
                                        'label' => Yii::t('app', 'Section'),
                                        'value' => function($model) {
                                            return $model->sectionName;
                                        },
                                    ],
                                    'title',
                                    'description:ntext',
                                    [
                                        'label' => Yii::t('app', 'Keywords'),
                                        'visible' => (bool)$model->keywords,
                                        'value' => function () use ($model) {
                                            $text = '';

                                            foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                                $text .= '<small class="badge badge-primary">' . $keyword . '</small>&nbsp';
                                            }

                                            return $text;
                                        },
                                        'format' => 'raw',
                                    ],
                                    [
                                        'attribute' => 'max_price',
                                        'value' => $model->max_price ? $model->max_price . ' ' . $model->currency->code : '∞',
                                    ],
                                    [
                                        'attribute' => 'location',
                                        'visible' => (bool)$model->location,
                                        'value' => function () use ($model) {
                                            return Html::a(
                                                $model->location,
                                                Url::to(['view-location', 'id' => $model->id]),
                                                ['class' => 'modal-btn-ajax']
                                            );
                                        },
                                        'format' => 'raw',
                                    ],
                                    [
                                        'attribute' => 'pickup_radius',
                                        'visible' => (bool)$model->pickup_radius,
                                        'value' => $model->pickup_radius . ' ' . Yii::t('app', 'km'),
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Offers'),
                                        'visible' => $model->getMatches()->count(),
                                        'format' => 'raw',
                                        'value' => function () use ($model) {
                                            return $model->getMatches()->count() ?
                                                Html::a(
                                                    $model->getMatches()->count(),
                                                    Url::to(['/ad-offer/show-matches', 'adSearchId' => $model->id]),
                                                ) : '';
                                        },
                                    ],
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$statusActiveUrl = Yii::$app->urlManager->createUrl(['/ad-search/set-active?id=' . $model->id]);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['/ad-search/set-inactive?id=' . $model->id]);

$script = <<<JS

$('.status-update').on("click", function(event) {
    const status = $(this).data('value');
    const active_url = '{$statusActiveUrl}';
    const inactive_url = '{$statusInactiveUrl}';
    const url = (parseInt(status) === 1) ? active_url : inactive_url;

        $.post(url, {}, function(result) {
            if (result === true) {
                location.reload();
            }
            else {

                $('#main-modal-header').text('Warning!');

                for (const [, errorMsg] of Object.entries(result)) {
                    $('#main-modal-body').append('<p>' + errorMsg + '</p>');
                }

                $('#main-modal').show();
                $('.close').on('click', function() {
                    $("#main-modal-body").html("");
                    $('#main-modal').hide();
                });
            }
        });

    return false;
});
JS;
$this->registerJs($script);
