<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Loyalty program';
$this->blocks['content-header'] = $this->title;

$this->registerJsFile('//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.min.js');

/** @var \app\models\User $user */
$user = Yii::$app->user->identity;
$inviteLink = Url::toRoute([
    '/invite/'.$user->id,
], true);

?>

    <div class="site-referrals pb-2">
        <div class="card bg-light">
            <div class="card-body">
              <p><?= Yii::t('app', 'All new users, who have joined the Website through your referral link, become your referrals.') ?> <?= Yii::t('app', 'You will get 1 User Rating for each new referral.') ?> <?= Yii::t('app', 'In the future, the loyalty program will be significantly increased and new bonuses will be added.') ?></p>
              <p><?= Yii::t('app', 'Soon:') ?></p>
              <p>
                  <ul>
                      <li><?= Yii::t('app', 'Multi-level loyalty program, you will receive rewards not only from first level of referrals, but also from next levels.') ?></li>
                      <li><?= Yii::t('app', 'You will receive rewards from your referrals for upgrading  their User ratings, purchases on the Website and websites of our partner companies.') ?></li>
                      <li><?= Yii::t('app', 'User community of the Website decides what conditions will be in loyalty program. You can participate in discuss process and vote for the conditions.') ?></li>
                  </ul>
              </p>
            </div>
        </div>
        <div class="card bg-light">
            <div class="card-header"><h2><?= Yii::t('app', 'Personal referral link') ?></h2></div>
            <div class="card-body">
                <p><?= Yii::t('app', 'Share your personal referral link with your friends and followers, and start earning now.') ?></p>
                <div class="input-group">
                    <input id="post-shortlink" class="form-control" value="<?= $inviteLink ?>">
                    <span class="input-group-btn">
                        <?= Html::button('<i class="fa fa-copy"></i>', [
                            'id' => 'copy-button',
                            'class' => 'btn btn-primary',
                            'data-clipboard-target' => '#post-shortlink',
                            'title' => 'Copy',
                        ]) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="card bg-light">
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        <h4 class="text-uppercase"><?= Yii::t('app', 'Referrals (Level 1)') ?></h4><?= Yii::t('app', 'People who have signed up using your link.') ?>
                        <p class="loyalty-count"><?= $user->getReferrals()->count() ?></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<?php
$this->registerJs("new Clipboard('#copy-button');");

$js = <<<'JS'

$('#post-shortlink').keydown(function(e) {
    if(!(e.ctrlKey == true && e.keyCode == 67)){
        e.preventDefault();
    }
}).focus(function() {
  $(this).select();
});

JS;

$this->registerJs($js);
