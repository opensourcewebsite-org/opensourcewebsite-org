<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\models\User;

/**
 * @var View $this
 * @var User $user
 * @var array $options
 */

?>

<div class="row" id="<?=$options['id']?>">
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
                                <?= Html::a('view', Url::to(['/contact/view', 'id' => $user->id])) ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="align-middle" scope="col"><?= Yii::t('app', 'Email') ?></th>
                            <td class="align-middle">
                                <?= Html::mailto($user->email, $user->email) ?>
                            </td>
                        </tr>
                        <?php if ($user->botUser && $user->botUser->provider_username) : ?>
                            <tr>
                                <th class="align-middle" scope="col"><?= Yii::t('app', 'Telegram') ?></th>
                                <td class="align-middle">
                                    <?= Html::a(
                                        '@' . $user->botUser->provider_username,
                                        'https://t.me/' . $user->botUser->provider_username
                                    ) ?>
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
