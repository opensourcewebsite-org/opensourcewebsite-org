<?php

declare(strict_types=1);

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var User $user
 * @var array $options
 */
?>
<div class="index" id="contact">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?=Yii::t('app', 'Contact')?></h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered detail-view mb-0">
                            <tbody>
                            <tr>
                                <th class="align-middle" scope="col"><?= Yii::t('app', 'Profile') ?></th>
                                <td class="align-middle">
                                    <?= Html::a(isset($user->username) ? '@' . $user->username : '#' . $user->id, Url::toRoute(['contact/view-user', 'id' => $user->id])) ?>
                                </td>
                            </tr>
                            <?php if ($userEmail = $user->email) : ?>
                                <tr>
                                    <th class="align-middle" scope="col"><?= Yii::t('app', 'Email') ?></th>
                                    <td class="align-middle">
                                        <?= Html::mailto($userEmail->email, $userEmail->email) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($user->botUser && $user->botUser->provider_user_name) : ?>
                                <tr>
                                    <th class="align-middle" scope="col"><?= Yii::t('app', 'Telegram') ?></th>
                                    <td class="align-middle">
                                        <?= Html::a('@' . $user->botUser->provider_user_name, 'https://t.me/' . $user->botUser->provider_user_name) ?>
                                    </td>
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
