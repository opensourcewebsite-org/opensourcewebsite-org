<?php

use app\components\helpers\Icon;
use app\components\helpers\TimeHelper;
use app\models\Country;
use app\models\Language;
use app\models\LanguageLevel;
use app\widgets\buttons\Trash;
use lo\widgets\modal\ModalAjax;
use yii\helpers\Url;
use kartik\select2\Select2Asset;
use app\widgets\buttons\Edit;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Account');

$timezones = TimeHelper::timezonesList();
Select2Asset::register($this);

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
                                        <?= Edit::widget([
                                            'url' => '/user/change-email',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Rank'); ?></th>
                                    <td class="align-middle"><?= "<b>$ranking[rank]</b> of $ranking[total]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Voting Power'); ?></th>
                                    <td class="align-middle"><?= "<b>$overallRating[percent]%</b> of 100%"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Rating'); ?></th>
                                    <td class="align-middle"><?= "<b>$overallRating[rating]</b> of $overallRating[totalRating]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Active Rating'); ?></th>
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
                                    <th class="align-middle"><?= Yii::t('app', 'Username'); ?></th>
                                    <td class="align-middle"><span id="username"><?= empty($model->username)
                                                ? '' : '<b>@</b>' . $model->username;
                                            ?></span></td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-username',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Name'); ?></th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-name',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Birthday'); ?></th>
                                    <td class="align-middle" id="birthday"><?= empty($model->birthday) ? '' :
                                            Yii::$app->formatter->asDate($model->birthday); ?></td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-birthday',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Gender'); ?></th>
                                    <td class="align-middle" id="gender">
                                        <?= Yii::t('app', $model->gender->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-gender',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Sexuality'); ?></th>
                                    <td class="align-middle" id="sexuality">
                                        <?= Yii::t('app', $model->sexuality->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-sexuality',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Timezone'); ?></th>
                                    <td class="align-middle" id="timezone"><?= Yii::t('app',
                                            $timezones[$model->timezone]); ?></td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-timezone',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Currency'); ?></th>
                                    <td class="align-middle" id="currency">
                                        <?= Yii::t('app', $model->currency->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= Edit::widget([
                                            'url' => '/user/change-currency',
                                            'options' => [
                                                'style' => 'float: right'
                                            ]
                                        ]); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Languages'); ?></th>
                                    <td class="align-middle" id="languages">
                                        <table>
                                            <?php
                                            array_map(function ($language) {
                                                $languageName = Language::findOne($language->language_id)->name;
                                                $languageName = Yii::t('app', $languageName);
                                                $languageLvl = LanguageLevel::findOne($language->language_level_id)->description;
                                                $languageLvl = Yii::t('app', $languageLvl);

                                                echo  '<tr><td>' . $languageName . ' - ' . $languageLvl . '</td><td>';
                                                echo ModalAjax::widget([
                                                    'id' => 'change-language' . $language->language_id,
                                                    'header' => Yii::t('app', 'Change language'),
                                                    'closeButton' => false,
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
                                        </table>
                                    </td>
                                    <td>
                                        <?= ModalAjax::widget([
                                            'id' => 'add-language',
                                            'header' => Yii::t('app', 'Add language'),
                                            'closeButton' => false,
                                            'toggleButton' => [
                                                'label' => Icon::ADD,
                                                'class' => 'btn btn-outline-success',
                                                'style' =>  ['float' => 'right'],
                                            ],
                                            'url' => Url::to(['user/add-language']),
                                            'ajaxSubmit' => true,
                                        ]);?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Citizenships'); ?></th>
                                    <td class="align-middle" id="citizenships">
                                        <table>
                                            <?php
                                            array_map(function ($citizenship) {
                                                    $citizenshipName = Country::findOne($citizenship->country_id)->name;
                                                    $citizenshipName = Yii::t('app', $citizenshipName);
                                                    echo '<tr><td>' . $citizenshipName . '</td><td>';
                                                    echo Trash::widget([
                                                            'url' => [
                                                                '/user/delete-citizenship',
                                                                'id' => $citizenship->country_id
                                                            ]
                                                        ]);
                                                    echo '</td></tr>';
                                            }, $model->citizenships); ?>
                                        </table>
                                    </td>
                                    <td>
                                        <?= ModalAjax::widget([
                                            'id' => 'add-citizenship',
                                            'header' => Yii::t('app', 'Add citizenship'),
                                            'closeButton' => false,
                                            'toggleButton' => [
                                                'label' => Icon::ADD,
                                                'class' => 'btn btn-outline-success',
                                                'style' =>  ['float' => 'right'],
                                            ],
                                            'url' => Url::to(['user/add-citizenship']),
                                            'ajaxSubmit' => true,
                                        ]);?>
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
