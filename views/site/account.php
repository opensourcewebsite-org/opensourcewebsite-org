<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use app\components\helpers\TimeHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
$this->title = 'Account';

$editProfileUrl = Yii::$app->urlManager->createUrl('user/edit-profile');

/**
 * Список для выбора пола пользователя
 */
$genders = [];

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

$JS = <<<JS
$('.edit-btn').click(function() {
    let modalName = $(this).data('modal');
    let modalWindow = $('#' + modalName);
    modalWindow.modal('toggle');
});

$('.save-btn').click(function() {
    let that = this;
    $.post(
        '{$editProfileUrl}',
        {
            'field': $(this).data('name'),
            'value': $('#' + $(this).data('name') + '-value').val()
        },
        function(result) {
            if(result) {
                let value = $('#' + $(that).data('name') + '-value').val();
                if($(that).data('name') == 'gender') {
                    switch ($('#' + $(that).data('name') + '-value').val()) {
                        case '0':
                            value = 'Male';
                            break;
                        case '1':
                            value = 'Female';
                            break;
                        default:
                            value = '';
                            break;
                    }
                }
                $('#' + $(that).data('name')).text(value);
                $('#' + $(that).data('modal')).modal('toggle');
                if($(that).data('name') == 'email') {
                    location.reload();
                }
            }
        });
});
JS;
$this->registerJs($JS);
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
            <div class="modal-body text-left">
                <input type="text" class="form-control" id="email-value">
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="email"
                        data-modal="change-email">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
            <div class="modal-body text-left">
                <input type="text" class="form-control" id="username-value">
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="username"
                        data-modal="change-username">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
            <div class="modal-body text-left">
                <input type="text" class="form-control" id="name-value">
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="name"
                        data-modal="change-name">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
            <div class="modal-body text-left">
                <?= DatePicker::widget([
                    'name'          => 'birthday',
                    'id'            => 'birthday-value',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format'    => 'mm/dd/yyyy',
                    ],
                ]); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="birthday"
                        data-modal="change-birthday">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
            <div class="modal-body text-left">
                <?= Html::dropDownList(
                    'gender-value',
                    null,
                    $genders,
                    [
                        'id'    => 'gender-value',
                        'class' => 'form-control'
                    ]); ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="gender"
                        data-modal="change-gender">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
            <div class="modal-body text-left">
                <?php
                $timezones = TimeHelper::timezonesList();

                echo Select2::widget([
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
                ]);
                ?>
                <div class="error"></div>
            </div>
            <div class="card-footer text-left">
                <button type="button" class="btn btn-success save-btn" data-name="timezone"
                        data-modal="change-timezone">Save</button>
                <a class="btn btn-secondary" href="#" title="Cancel" data-dismiss="modal" >Cancel</a>
            </div>
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
                                    <td class="align-middle" id="email"><?= $model->email; ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'email',
                                                'data-modal' => 'change-email'
                                            ]); ?>
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
                                    <td class="align-middle"><?= "<b>$activeRating</b> (" . Yii::t('bot', 'in the last {0,number} days', 30) . ")"; ?></td>
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
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'username',
                                                'data-modal' => 'change-username'
                                            ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Name</th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'name',
                                                'data-modal' => 'change-name'
                                            ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Birthday</th>
                                    <td class="align-middle" id="birthday"><?= date('m/d/Y', strtotime
                                        ($model->birthday));
                                        ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'birthday',
                                                'data-modal' => 'change-birthday'
                                            ]); ?>
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
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'gender',
                                                'data-modal' => 'change-gender'
                                            ]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle">Timezone</th>
                                    <td class="align-middle" id="timezone"><?= $model->timezone; ?></td>
                                    <td>
                                        <?= Html::button(
                                            '<i class="fas fa-edit"></i>',
                                            [
                                                'class'      => 'btn btn-light edit-btn',
                                                'title'      => 'Edit',
                                                'style'      => ['float' => 'right'],
                                                'data-name'  => 'timezone',
                                                'data-modal' => 'change-timezone'
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


