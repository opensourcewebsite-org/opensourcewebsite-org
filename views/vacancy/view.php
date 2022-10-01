<?php

declare(strict_types=1);

use app\components\helpers\ArrayHelper;
use app\components\helpers\Html;
use app\models\Language;
use app\models\LanguageLevel;
use app\models\Vacancy;
use app\widgets\buttons\EditButton;
use app\widgets\ModalAjax;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Vacancy */

$this->title = Yii::t('app', 'Vacancy') . ' #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vacancies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;
?>
<div class="index">
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
                                           data-value="<?= Vacancy::STATUS_ON ?>">
                                            <?= Yii::t('app', 'Active') ?>
                                        </a>
                                        <a class="dropdown-item status-update <?= $model->isActive() ? '' : 'active' ?>"
                                           href="#"
                                           data-value="<?= Vacancy::STATUS_OFF ?>">
                                            <?= Yii::t('app', 'Inactive') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item align-self-center mr-3">
                            <?= EditButton::widget([
                                'url' => ['vacancy/update', 'id' => $model->id],
                                'options' => [
                                    'title' => 'Edit Vacancy',
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
                                'name',
                                'responsibilities:ntext',
                                'requirements:ntext',
                                'conditions:ntext',
                                [
                                    'label' => Yii::t('app', 'Keywords'),
                                    'visible' => (bool)$model->keywords,
                                    'value' => function () use ($model) {
                                        $text = '';

                                        foreach (ArrayHelper::getColumn($model->keywords, 'keyword') as $keyword) {
                                            $text .= Html::tag('span', $keyword, ['class' => 'badge badge-primary']) . '&nbsp';
                                        }

                                        return $text;
                                    },
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'max_hourly_rate',
                                    'value' => $model->max_hourly_rate ? $model->max_hourly_rate . ' ' . $model->currency->code : '∞',
                                ],
                                'remote_on:boolean',
                                [
                                    'label' => Yii::t('jo', 'Offline work'),
                                    'value' => (bool)$model->location ? Yii::t('app', 'Yes') : Yii::t('app', 'No'),
                                ],
                                [
                                    'attribute' => 'location',
                                    'visible' => (bool)$model->location,
                                    'value' => function () use ($model) {
                                        return Html::a(
                                            $model->location,
                                            Url::to(['view-location', 'id' => $model->id]),
                                            ['class' => 'modal-btn-ajax']
                                        ) ;
                                    },
                                    'format' => 'raw',
                                ],
                                [
                                    'attribute' => 'gender_id',
                                    'visible' => (bool)$model->gender_id,
                                    'value' => function () use ($model) {
                                        return $model->gender ? $model->gender->name : '';
                                    }
                                ],
                                [
                                    'label' => Yii::t('app', 'Offers'),
                                    'visible' => $model->getMatches()->exists(),
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        return ($matchesCount = $model->getMatches()->count()) ?
                                            Html::a(
                                                $model->getNewMatches()->exists() ? Html::badge('info', 'new') : $matchesCount,
                                                Url::to(['/resume/matches', 'vacancyId' => $model->id]),
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

<div class="languages-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('jo', 'Required languages'); ?></h3>
                    <div class="card-tools">
                        <?= ModalAjax::widget([
                            'id' => 'add-language',
                            'header' => Yii::t('user', 'Add language'),
                            'toggleButton' => [
                                'label' => Html::icon('add'),
                                'class' => 'btn btn-outline-success',
                                'style' =>  [
                                    'float' => 'right',
                                ],
                            ],
                            'url' => Url::to([
                                'vacancy/add-language',
                                'vacancyId' => $model->id,
                            ]),
                        ]);?>
                    </div>
                </div>
                <?php if ($model->languages): ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php foreach ($model->languages as $vacancyLanguage) : ?>
                                    <tr>
                                        <td><?= $vacancyLanguage->getLabel() ?></td>
                                        <td><?= ModalAjax::widget([
                                            'id' => 'change-language' . $vacancyLanguage->language_id,
                                            'header' => Yii::t('user', 'Edit language'),
                                            'toggleButton' => [
                                                'label' => Html::icon('edit'),
                                                'title' => Yii::t('app', 'Edit'),
                                                'class' => 'btn btn-light edit-btn',
                                                'style' =>  [
                                                    'float' => 'right',
                                                ],
                                            ],
                                            'url' => Url::to([
                                                'vacancy/change-language',
                                                'id' => $vacancyLanguage->id,
                                                'vacancyId' => $vacancyLanguage->vacancy_id,
                                            ]),
                                        ]); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($model->company_id): ?>
    <div class="index">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('app', 'Company') ?></h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?= DetailView::widget([
                                'model' => $company = $model->company,
                                'attributes' => [
                                    'id',
                                    [
                                        'attribute' => 'name',
                                        'visible' => (bool)$company->name,
                                    ],
                                    [
                                        'attribute' => 'url',
                                        'visible' => (bool)$company->url,
                                        'format' => 'url',
                                    ],
                                    [
                                        'attribute' => 'address',
                                        'visible' => (bool)$company->address,
                                    ],
                                    [
                                        'attribute' => 'description',
                                        'visible' => (bool)$company->description,
                                        'format' => 'ntext',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
$statusActiveUrl = Yii::$app->urlManager->createUrl(['vacancy/set-active?id=' . $model->id]);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['vacancy/set-inactive?id=' . $model->id]);

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
