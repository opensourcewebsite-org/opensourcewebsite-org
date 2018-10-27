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
              <p><?= Yii::t('app', 'All new users, who have joined our website through your referral link, become your referrals. You will get 1 rating for each new referral. In the future, the loyalty program will be significantly increased and new bonuses will be added.') ?></p>
            </div>
        </div>
        <div class="card bg-light">
            <div class="card-header"><h2><?= Yii::t('app', 'Personal referral link') ?></h2></div>
            <div class="card-body">
                <p><?= Yii::t('app', 'Share your personal referral link with your friends and followers.') ?></p>
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
                        <h4 class="text-uppercase"><?= Yii::t('app', 'Referrals (1 level)') ?></h4><?= Yii::t('app', 'People who have signed up using your link.') ?>
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
