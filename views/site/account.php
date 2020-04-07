<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use app\components\helpers\TimeHelper;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use app\models\profile\Email;
use app\models\profile\Username;
use app\models\profile\Name;
use app\models\profile\Birthday;
use app\models\profile\Gender;
use app\models\profile\Timezone;
use app\models\profile\Currency;
use app\models\profile\Sexuality;

/* @var $this yii\web\View */
$this->title = 'Account';

$editProfileUrl = Yii::$app->urlManager->createUrl('user/edit-profile');

/**
 * Список для выбора пола пользователя
 */
$genders = [];

/**
 * Список для выбора валюты пользователя
 */
$currencies = [];

/**
 * Список для выбора валюты пользователя
 */
$sexualities = [];

/**
 * Текущий пол пользователя
 */
$userGender = '';
foreach ($genderList as $key => $value) {
    $genders[$key] = $value->name;
    if ($key == $model->gender_id) {
        $userGender = $value->name;
    }
}

/**
 * Текущая валюта пользователя
 */
$userCurrency = '';
foreach ($currencyList as $key => $value) {
    $currencies[$key] = $value->name;
    if ($key == $model->currency_id) {
        $userCurrency = $value->name;
    }
}

/**
 * Текущая ориентация пользователя
 */
$userSexuality = '';
foreach ($sexualityList as $key => $value) {
    $sexualities[$key] = $value->name;
    if ($key == $model->sexuality_id) {
        $userSexuality = $value->name;
    }
}
?>
<!-- Модальное окно для email -->
<div class="modal fade" id="change-email" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change email</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $emailModel = new Email;
            $emailForm = ActiveForm::begin(['action' => 'user/change-email']);
            ?>
                <div class="modal-body text-left">
                    <?= $emailForm->field($emailModel, 'email')->input('email')->label('Email'); ?>
                    <div class="error"></div>
                </div>
                <div class="card-footer text-left">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                    <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для username -->
<div class="modal fade" id="change-username" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change username</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $usernameModel = new Username;
            $usernameForm = ActiveForm::begin(['action' => 'user/change-username']);
            ?>
            <div class="modal-body text-left">
                <?= $usernameForm->field($usernameModel, 'username')->textInput()->label('Username'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для name -->
<div class="modal fade" id="change-name" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change name</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $nameModel = new Name;
            $nameForm = ActiveForm::begin(['action' => 'user/change-name']);
            ?>
            <div class="modal-body text-left">
                <?= $nameForm->field($nameModel, 'name')->textInput()->label('Name'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для birthday -->
<div class="modal fade" id="change-birthday" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change birthday</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $birthdayModel = new Birthday;
            $birthdayForm = ActiveForm::begin(['action' => 'user/change-birthday']);
            ?>
            <div class="modal-body text-left">
                <?= $birthdayForm->field($birthdayModel, 'birthday')->widget(DatePicker::class, [
                    'name'          => 'birthday',
                    'id'            => 'birthday-value',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'mm/dd/yyyy',
                    ],
                ])->label('Birthday'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для gender -->
<div class="modal fade" id="change-gender" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change gender</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $genderModel = new Gender;
            $genderForm = ActiveForm::begin(['action' => 'user/change-gender']);
            ?>
            <div class="modal-body text-left">
                <?= $genderForm->field($genderModel, 'gender')->dropDownList($genders)->label('Gender'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для sexuality -->
<div class="modal fade" id="change-sexuality" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change sexuality</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $sexualityModel = new Sexuality;
            $sexualityForm = ActiveForm::begin(['action' => 'user/change-sexuality']);
            ?>
            <div class="modal-body text-left">
                <?= $sexualityForm->field($sexualityModel, 'sexuality')->dropDownList($sexualities)->label('Sexuality'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для timezone -->
<div class="modal fade" id="change-timezone" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change timezone</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $timezoneModel = new Timezone;
            $timezoneForm = ActiveForm::begin(['action' => 'user/change-timezone']);
            ?>
            <div class="modal-body text-left">
                <?php
                $timezones = TimeHelper::timezonesList();

                echo $timezoneForm->field($timezoneModel, 'timezone')->widget(Select2::class, [
                    'name'    => 'change-timezone',
                    'value'   => '',
                    'data'    => array_combine(
                        array_values($timezones),
                        array_values($timezones)
                    ),
                    'options' => [
                        'id'     => 'timezone-value',
                        'prompt' => '',
                    ],
                ])->label('Timezone');
                ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Модальное окно для currency -->
<div class="modal fade" id="change-currency" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change currency</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $currencyModel = new Currency;
            $currencyForm = ActiveForm::begin(['action' => 'user/change-currency']);
            ?>
            <div class="modal-body text-left">
                <?= $currencyForm->field($currencyModel, 'currency')->dropDownList($currencies)->label('Currency'); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

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
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'email',
                                                'data-modal'  => 'change-email',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-email',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Rank</th>
                                    <td class="align-middle"><?= "<b>$ranking[rank]</b> of $ranking[total]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Voting Power</th>
                                    <td class="align-middle"><?= "<b>$overallRating[percent]%</b> of 100%"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Rating</th>
                                    <td class="align-middle"><?= "<b>$overallRating[rating]</b> of $overallRating[totalRating]"; ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Active Rating</th>
                                    <td class="align-middle"><?= "<b>$activeRating</b> (" . Yii::t('bot', 'in the last {0,number} days', 30) . ')'; ?></td>
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
            <h2 style="padding-top: 30px; padding-bottom: 15px">Profile</h2>
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
                                    <th class="align-middle">Username</th>
                                    <td class="align-middle"><b>@</b><span id="username"><?= $model->username;
                                            ?></span></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'username',
                                                'data-modal'  => 'change-username',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-username',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Name</th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'name',
                                                'data-modal'  => 'change-name',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-name',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Birthday</th>
                                    <td class="align-middle" id="birthday"><?= date('m/d/Y', strtotime
                                        ($model->birthday)); ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'birthday',
                                                'data-modal'  => 'change-birthday',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-birthday',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Gender</th>
                                    <td class="align-middle" id="gender">
                                        <?= $userGender; ?>
                                    </td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'gender',
                                                'data-modal'  => 'change-gender',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-gender',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Sexuality</th>
                                    <td class="align-middle" id="sexuality">
                                        <?= $userSexuality; ?>
                                    </td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'sexuality',
                                                'data-modal'  => 'change-sexuality',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-sexuality',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Timezone</th>
                                    <td class="align-middle" id="timezone"><?= $model->timezone; ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'timezone',
                                                'data-modal'  => 'change-timezone',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-timezone',
                                            ]);
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Currency</th>
                                    <td class="align-middle" id="currency">
                                        <?= $userCurrency; ?>
                                    </td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'       => 'btn btn-light edit-btn',
                                                'title'       => 'Edit',
                                                'style'       => ['float' => 'right'],
                                                'data-name'   => 'currency',
                                                'data-modal'  => 'change-currency',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#change-currency',
                                            ]);
                                        ?>
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
