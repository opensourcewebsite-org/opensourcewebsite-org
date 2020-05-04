<?php

use app\components\helpers\TimeHelper;
use app\models\Country;
use app\models\Language;
use app\models\LanguageLevel;
use app\modules\bot\components\helpers\Emoji;
use yii\helpers\Html;
use lo\widgets\modal\ModalAjax;
use yii\helpers\Url;
use kartik\select2\Select2Asset;

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
                                    <td class="align-middle"><span id="username"><?= empty($model->username)
                                                ? '' : '<b>@</b>' . $model->username;
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
                                    <td class="align-middle" id="birthday"><?= empty($model->birthday) ? '' :
                                            Yii::$app->formatter->asDate($model->birthday); ?></td>
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
                                        <?= Yii::t('app', $model->gender->name ?? ''); ?>
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
                                        <?= Yii::t('app', $model->sexuality->name ?? ''); ?>
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
                                    <td class="align-middle" id="timezone"><?= Yii::t('app',
                                            $timezones[$model->timezone]); ?></td>
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
                                        <?= Yii::t('app', $model->currency->name ?? ''); ?>
                                    </td>
                                    <td>
                                        <?= Html::button('<a href="/user/change-currency" class="fas fa-edit"></a>', [
                                            'class' => 'btn btn-light edit-btn',
                                            'title' => Yii::t('app', 'Edit'),
                                            'style' => ['float' => 'right']]); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th class="align-middle"><?= Yii::t('app', 'Languages'); ?></th>
                                    <td class="align-middle" id="languages">
                                        <table>
                                            <?php array_map(function ($language) {
                                                $languageName = Language::findOne($language->language_id)->name;
                                            $languageName = Yii::t('app', $languageName);
                                            $languageLvl = LanguageLevel::findOne($language->language_level_id)->description;
                                            $languageLvl = Yii::t('app', $languageLvl);

                                            echo  "<tr><td>$languageName - $languageLvl</td><td>" .
                                            ModalAjax::widget([
                                                'id' => 'change-language' . $language->language_id,
                                                'header' => Yii::t('app', 'Change language'),
                                                'closeButton' => false,
                                                'toggleButton' => [
                                                    'label' => '<a class="fas fa-edit"></a>',
                                                    'class' => 'btn btn-light edit-btn',
                                                    'style' =>  ['float' => 'right', 'color' => '#007bff'],
                                                ],
                                                'url' => Url::to(['user/change-language', 'id' => $language->language_id]),
                                                'ajaxSubmit' => true,
                                            ])
                                            . "</td></tr>";
                                            }, $model->languages);
                                        ?>
                                        </table>
                                    </td>
                                    <td>
                                        <?= ModalAjax::widget([
                                            'id' => 'add-language',
                                            'header' => Yii::t('app', 'Add language'),
                                            'closeButton' => false,
                                            'toggleButton' => [
                                                'label' => '<i class="fa fa-plus"></i>',
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
                                            <?php array_map(function ($citizenship) {
                                                $citizenshipName = Country::findOne($citizenship->country_id)->name;
                                                $citizenshipName = Yii::t('app', $citizenshipName);
                                                echo  "<tr><td>$citizenshipName</td><td>" .
                                                    Html::a('<i class="fa fa-trash"></i>',
                                                        ['/user/delete-citizenship', 'id' => $citizenship->country_id], [
                                                        'title'        => Yii::t('yii', 'Delete'),
                                                        'aria-label'   => Yii::t('yii', 'Delete'),
                                                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                                        'data-pjax'    => '1',
                                                        'data-method'  => 'post',
                                                        'class'        => 'btn-action',
                                                    ]) . '</td></tr>';
                                                }, $model->citizenships);
                                            ?>
                                        </table>
                                    </td>
                                    <td>
                                        <?= ModalAjax::widget([
                                            'id' => 'add-citizenship',
                                            'header' => Yii::t('app', 'Add citizenship'),
                                            'closeButton' => false,
                                            'toggleButton' => [
                                                'label' => '<i class="fa fa-plus"></i>',
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
