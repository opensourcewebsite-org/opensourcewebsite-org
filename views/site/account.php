<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Account');
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
                                        <?= Html::button('<a href="/user/change-email" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
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
                                    <td class="align-middle"><b>@</b><span id="username"><?= empty($model->username)
                                                ? $model->id : $model->username;
                                    ?></span></td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-username" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Name'); ?></th>
                                    <td class="align-middle" id="name"><?= $model->name ?? $model->id; ?></td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-name" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Birthday'); ?></th>
                                    <td class="align-middle" id="birthday"><?= empty($model->birthday) ? '' : date('Y/m/d', strtotime($model->birthday)); ?></td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-birthday" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Gender'); ?></th>
                                    <td class="align-middle" id="gender">
                                        <?= $model->gender->name ?? ''; ?>
                                    </td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-gender" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Sexuality'); ?></th>
                                    <td class="align-middle" id="sexuality">
                                        <?= $model->sexuality->name ?? ''; ?>
                                    </td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-sexuality" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Timezone'); ?></th>
                                    <td class="align-middle" id="timezone"><?= $model->timezone; ?></td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-timezone" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Currency'); ?></th>
                                    <td class="align-middle" id="currency">
                                        <?= $model->currency->name  ?? ''; ?>
                                    </td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-currency" class="fas fa-edit"></a>', [
                                                'class' => 'btn btn-light edit-btn',
                                                'title' => Yii::t('app', 'Edit'),
                                                'style' => ['float' => 'right']]); ?>
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
