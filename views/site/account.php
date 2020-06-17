<?php

use app\components\helpers\Icon;
use app\components\helpers\TimeHelper;
use app\models\Country;
use app\models\Language;
use app\models\LanguageLevel;
use app\widgets\buttons\TrashButton;
use app\widgets\ModalAjax;
use yii\helpers\Url;
use app\widgets\buttons\EditButton;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Account');

$timezones = TimeHelper::timezonesList();

?>

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
                                    <td class="align-middle"><?= $model->id; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Real confirmations'); ?></th>
                                    <td class="align-middle"><?= $realConfirmations; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Email</th>
                                    <td class="align-middle" id="email">
                                        <?php
                                        echo $model->email;
                                        if (!$model->is_authenticated) {
                                            echo ' <b>(not confirmed)</b>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-email',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Rank'); ?></th>
                                    <td class="align-middle"><?= "<b>$ranking[rank]</b> of $ranking[total]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Voting Power'); ?></th>
                                    <td class="align-middle"><?= "<b>$overallRating[percent]%</b> of 100%"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Html::a(Yii::t('user', 'Rating'), ['user/rating']); ?></th>
                                    <td class="align-middle"><?= "<b>$overallRating[rating]</b> of $overallRating[totalRating]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Active Rating'); ?></th>
                                    <td class="align-middle"><?= "<b>$model->activeRating</b> (" . Yii::t('bot', 'in the last {0,number} days', 30) . ')'; ?></td>
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
        <div class="col-md-12">
            <h2 style="padding-top: 30px; padding-bottom: 15px"><?= Yii::t('app', 'Profile') ?></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div id="w0" class="grid-view">
                            <table class="table table-condensed table-hover" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Username'); ?></th>
                                    <td class="align-middle"><span id="username"><?= empty($model->username)
                                                ? '' : '<b>@</b>' . $model->username;
                                            ?></span></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-username',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Name'); ?></th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-name',
                                            'options' => [
                                                'style' => 'float: right'
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
                                                'style' => 'float: right'
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
                                                'style' => 'float: right'
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
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Timezone'); ?></th>
                                    <td class="align-middle" id="timezone"><?= Yii::t('app',
                                            $timezones[$model->timezone]); ?></td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-timezone',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('user', 'Currency'); ?></th>
                                    <td class="align-middle" id="currency">
                                        <?= Yii::t('app', $model->currency->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= EditButton::widget([
                                            'url' => '/user/change-currency',
                                            'options' => [
                                                'style' => 'float: right'
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
                                'label' => Icon::ADD,
                                'class' => 'btn btn-outline-success',
                                'style' =>  ['float' => 'right'],
                            ],
                            'url' => Url::to(['user/add-language']),
                            'ajaxSubmit' => true,
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
                                    $languageName = Language::findOne($language->language_id)->name;
                                    $languageName = Yii::t('app', $languageName);
                                    $languageLevel = LanguageLevel::findOne($language->language_level_id)->description;
                                    $languageLevel = Yii::t('user', $languageLevel);

                                    echo  '<tr><td>' . $languageName . ' - ' . $languageLevel . '</td><td>';
                                    echo ModalAjax::widget([
                                        'id' => 'change-language' . $language->language_id,
                                        'header' => Yii::t('user', 'Change language'),
                                        'toggleButton' => [
                                            'label' => Icon::EDIT,
                                            'class' => 'btn btn-light edit-btn',
                                            'style' =>  ['float' => 'right', 'color' => '#007bff'],
                                        ],
                                        'url' => Url::to([
                                            'user/change-language',
                                            'id' => $language->language_id]),
                                        'ajaxSubmit' => true,
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
                                'label' => Icon::ADD,
                                'class' => 'btn btn-outline-success',
                                'style' =>  ['float' => 'right'],
                            ],
                            'url' => Url::to(['user/add-citizenship']),
                            'ajaxSubmit' => true,
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
                                    $citizenshipName = Country::findOne($citizenship->country_id)->name;
                                    $citizenshipName = Yii::t('user', $citizenshipName);
                                    echo '<tr><td>' . $citizenshipName . '</td><td>';
                                    echo TrashButton::widget([
                                        'url' => [
                                            '/user/delete-citizenship',
                                            'id' => $citizenship->country_id,
                                        ],
                                        'options' => [
                                            'style' => 'float: right',
                                        ]
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
