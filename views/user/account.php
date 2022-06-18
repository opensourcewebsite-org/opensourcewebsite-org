<?php

use app\components\helpers\TimeHelper;
use app\models\Country;
use app\models\Language;
use app\models\LanguageLevel;
use app\widgets\buttons\TrashButton;
use app\widgets\ModalAjax;
use yii\helpers\Url;
use app\widgets\buttons\EditButton;
use app\components\helpers\Html;
use app\components\helpers\ExternalLink;
use app\models\User;
use app\models\StellarOperator;
use app\widgets\buttons\SelectButton;
use yii\widgets\ActiveForm;
use app\widgets\LocationPickerWidget\LocationPickerViewWidget;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Account');
?>
<link href="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet.min.css" rel="stylesheet">
<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet-src.min.js"></script>

<div class="account-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                    <tr>
                                        <th class="align-middle">ID</th>
                                        <td class="align-middle">#<?= $model->id ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Username'); ?></th>
                                        <td class="align-middle"><span id="username"><?= $model->username ? '@' . $model->username : ''; ?></span></td>
                                        <td>
                                            <?= EditButton::widget([
                                                'url' => '/user/change-username',
                                                'options' => [
                                                    'style' => 'float: right',
                                                ],
                                            ]); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Rank'); ?></th>
                                        <td class="align-middle"><b><?= $model->getRank() ?></b> <?= Yii::t('app', 'of'); ?> <?= User::getTotalRank(); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Voting Power'); ?></th>
                                        <td class="align-middle"><b><?= $model->getRatingPercent() ?> %</b> <?= Yii::t('app', 'of'); ?> 100%</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">
                                        <?php
                                            $string = Yii::t('user', 'Rating');

                                            if ($model->ratings) {
                                                echo Html::a($string, ['user/rating']);
                                            } else {
                                                echo $string;
                                            }
                                        ?>
                                        </th>
                                        <td class="align-middle"><b><?= $model->getRating() ?></b> <?= Yii::t('app', 'of'); ?> <?= User::getTotalRating(); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Active Rating'); ?></th>
                                        <td class="align-middle"><b><?= $model->getActiveRating() ?></b>&nbsp;(<?= Yii::t('bot', 'in the last {0,number} days', Yii::$app->settings->days_count_to_calculate_active_rating); ?>)</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th class="align-middle"><?= Yii::t('user', 'Real confirmations'); ?></th>
                                        <td class="align-middle"><?= $model->getRealConfirmations() ?></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="profile-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Name'); ?></th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-name',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Birthday'); ?></th>
                                    <td class="align-middle" id="birthday"><?= empty($model->birthday) ? '' :
                                            Yii::$app->formatter->asDate($model->birthday); ?></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-birthday',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Gender'); ?></th>
                                    <td class="align-middle" id="gender">
                                        <?= Yii::t('user', $model->gender->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-gender',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Sexuality'); ?></th>
                                    <td class="align-middle" id="sexuality">
                                        <?= Yii::t('user', $model->sexuality->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-sexuality',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Timezone'); ?></th>
                                    <td class="align-middle" id="timezone"><?= TimeHelper::getNameByOffset($model->timezone); ?></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-timezone',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Currency'); ?></th>
                                    <td class="align-middle" id="currency">
                                        <?= $model->currency ? ($model->currency->code . ' - ' . $model->currency->name) : '' ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-currency',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Location'); ?></th>
                                    <td class="align-middle" id="currency">
                                        <?php
                                        $form = ActiveForm::begin();
                                            echo $form->field($model, 'location')
                                                ->widget(LocationPickerViewWidget::class)
                                                ->label(false);
                                            ActiveForm::end();
                                        ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-location',
                                            'options' => [
                                                'style' => 'float: right',
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
                    <h3 class="card-title"><?= Yii::t('user', 'Languages'); ?></h3>
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
                                'user/add-language',
                            ]),
                        ]);?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php
                                array_map(function ($language) {
                                    echo '<tr><td>' . $language->getLabel() . '</td><td>';
                                    echo ModalAjax::widget([
                                        'id' => 'change-language' . $language->language_id,
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
                                            'user/change-language',
                                            'id' => $language->id]),
                                    ]);
                                    echo '</td></tr>';
                                }, $model->languages); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="citizenship-index">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('user', 'Citizenships'); ?></h3>
                    <div class="card-tools">
                        <?= ModalAjax::widget([
                            'id' => 'add-citizenship',
                            'header' => Yii::t('user', 'Add citizenship'),
                            'toggleButton' => [
                                'label' => Html::icon('add'),
                                'class' => 'btn btn-outline-success',
                                'style' =>  [
                                    'float' => 'right',
                                ],
                            ],
                            'url' => Url::to([
                                'user/add-citizenship',
                            ]),
                        ]);?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <?php
                                array_map(function ($citizenship) {
                                    echo '<tr><td>' . Yii::t('user', $citizenship->country->name) . '</td><td>';
                                    echo TrashButton::widget([
                                        'url' => [
                                            '/user/delete-citizenship',
                                        ],
                                        'options' => [
                                            'style' => 'float: right',
                                            'data-params' => [
                                                'id' => $citizenship->country_id,
                                            ],
                                        ],
                                    ]);
                                    echo '</td></tr>';
                                }, $model->citizenships); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="contact-box">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Yii::t('user', 'Contacts'); ?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <th class="align-middle">Email</th>
                                    <td class="align-middle">
                                        <?php if ($userEmail = $model->email) : ?>
                                            <?= (!$userEmail->isConfirmed() ? Html::badge('warning', Yii::t('app', 'not confirmed')) . ' ' : '') . $userEmail->email ?>
                                            <?php if (!$userEmail->isConfirmed()) : ?>
                                            <br/><br/>
                                            <?= Html::icon('warning') ?> <?= Yii::t('bot', 'Confirm your Email') ?>.<br/>
                                            <br/>
                                            <?= Yii::t('bot', 'An email with a confirmation link was sent to your email address') ?>. <?= Yii::t('bot', 'In order to complete the process, please click the confirmation link') ?>.<br/>
                                            <br/>
                                            <?= Yii::t('bot', 'If you do not receive a confirmation email, please check your spam folder') ?>.
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-email',
                                            'options' => [
                                                'style' => 'float: right',
                                            ],
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Stelar</th>
                                    <td class="align-middle">
                                        <?php if (($userStellar = $model->stellar) && !$userStellar->isExpired()) : ?>
                                            <?= (!$userStellar->isConfirmed() ? Html::badge('warning', Yii::t('app', 'not confirmed')) . ' ' : '') . Html::a($userStellar->getPublicKey(), ExternalLink::getStellarExpertAccountLink($userStellar->getPublicKey())) ?>
                                            <?php if (!$userStellar->isConfirmed()) : ?>
                                            <br/><br/>
                                            <?= Html::icon('warning') ?> <?= Yii::t('bot', 'Confirm your Stellar account') ?> (<?= Yii::t('bot', 'added {0}', Yii::$app->formatter->asRelativeTime($userStellar->created_at)) ?>).<br/>
                                            <?php if (StellarOperator::getDistributorPublicKey()) : ?>
                                            <br/>
                                            <?= Yii::t('bot', 'In the next {0,number} minutes, send any amount of XLM to OSW account {1} and then click the "CONFIRM" button', [$userStellar->getTimeLimit(), Html::a(StellarOperator::getDistributorPublicKey(), ExternalLink::getStellarExpertAccountLink(StellarOperator::getDistributorPublicKey()))]) ?>.<br/>
                                            <br/>
                                            <?= SelectButton::widget([
                                                'text' => Yii::t('app', 'Confirm'),
                                                'options' => [
                                                    'title' => Yii::t('app', 'Confirm'),
                                                    'style' => '',
                                                    'class' => 'btn btn-outline-success',
                                                    // 'data-toggle' => 'modal',
                                                    // 'data-target' => '#main-modal',
                                                ],
                                                'url' => [
                                                    'user/confirm-stellar',
                                                ],
                                            ]); ?>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-stellar',
                                            'options' => [
                                                'style' => 'float: right',
                                            ],
                                        ]); ?>
                                    </td>
                                </tr>
                                <?php if (($telegramUser = $model->botUser) && ($telegramUsername = $telegramUser->getUsername())) : ?>
                                    <tr>
                                        <th class="align-middle">Telegram</th>
                                        <td class="align-middle"><?= Html::a('@' . $telegramUsername, ExternalLink::getTelegramAccountLink($telegramUsername)); ?></td>
                                        <td></td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
