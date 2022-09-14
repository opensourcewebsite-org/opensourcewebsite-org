<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\models\StellarGiver;
use app\components\helpers\Html;

/**
 * @var View $this
 */

$this->title = Yii::t('bot', 'Basic Income');
$this->params['breadcrumbs'][] = 'Stellar';

$stellarGiver = new StellarGiver();
?>
<?php if (StellarGiver::getGiverPublicKey()) : ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex p-0">
                <div class="col-sm-12">
                    <?= $this->render('_navbar'); ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex p-0">
                                <ul class="nav nav-pills ml-auto p-2">
                                    <li class="nav-item align-self-center mr-3">
                                        <div class="input-group-prepend">
                                            <div class="dropdown">
                                                <a class="btn <?= $user->isBasicIncomeOn() ? 'btn-primary' : 'btn-default' ?> dropdown-toggle"
                                                   href="#" role="button"
                                                   id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                                                   aria-expanded="false">
                                                    <?= $user->isBasicIncomeOn() ?
                                                        Yii::t('app', 'Active') :
                                                        Yii::t('app', 'Inactive') ?>
                                                </a>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                    <h6 class="dropdown-header"><?= Yii::t('app', 'Participation') ?></h6>

                                                    <a class="dropdown-item status-update <?= $user->isBasicIncomeOn() ? 'active' : '' ?>"
                                                       href="#"
                                                       data-value="1">
                                                        <?= Yii::t('app', 'Active') ?>
                                                    </a>

                                                    <a class="dropdown-item status-update <?= $user->isBasicIncomeOn() ? '' : 'active' ?>"
                                                       href="#"
                                                       data-value="0">
                                                        <?= Yii::t('app', 'Inactive') ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div>
                                    <?php if ($user->isBasicIncomeOn()) : ?>
                                    <?php if ($user->stellar && $user->stellar->isConfirmed()) : ?>
                                    <?php if ($user->isBasicIncomeActivated()) : ?>
                                    <?= Html::icon('on') ?> <?= Yii::t('bot', 'You are a participant of this program and receive a weekly basic income') ?>.
                                    <?php else : ?>
                                    <?= Html::icon('pending') ?> <?= Yii::t('bot', 'You are a candidate for this program and your application is pending review by other users') ?>.
                                    <?php endif; ?>
                                    <?php else : ?>
                                    <?= Html::icon('warning') ?> <?= Yii::t('bot', 'Confirm your Stellar account in order for your application to be processed') ?>.
                                    <?php endif; ?>
                                    <?php else : ?>
                                    <?= Html::icon('off') ?> <?= Yii::t('bot', 'You have refused to participate in this program') ?>.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?= Yii::t('bot', 'Weekly payment to each participant') ?>: <b><?= $stellarGiver->getPaymentAmount() ?> XLM</b><br/>
                <br/>
                <?= Yii::t('bot', 'Total participants') ?>: <b><?= $stellarGiver->getParticipantsCount() ?></b><br/>
                <?= Yii::t('bot', 'Weekly Basic Income Fund') ?>: <b><?= $stellarGiver->getAvailableBalance() ?> XLM</b><br/>
                <br/>
                <?= Yii::t('bot', 'Start earning a weekly basic income every Friday') ?>. <?= Yii::t('bot', 'Weekly {0}% of the total basic income fund is sent in equal parts to all eligible participants', StellarGiver::WEEKLY_PAYMENT_PERCENT) ?>. <?= Yii::t('bot', 'The total basic income fund is formed by donations from people who support the principles and values of the free society') ?>.<br/>
                <br/>
                <?= Yii::t('bot', 'Any person who meets these criteria can become a participant in a free society and receive a weekly basic income') ?>:<br/>
                <br/>
                • <?= Html::a(Yii::t('bot', 'adhere to the principles and values of a free society'), 'https://en.wikipedia.org/wiki/Non-aggression_principle') ?>.<br/>
                • <?= Html::a(Yii::t('bot', 'be a resident of Montenegro'), 'https://en.wikipedia.org/wiki/Montenegro') ?>.<br/>
                • <?= Html::a(Yii::t('bot', 'be fully capable'), 'https://en.wikipedia.org/wiki/Capacity_(law)') ?>.<br/>
                <br/>
                <?= Yii::t('bot', 'To support the free society and increase the weekly payments to its participants, send any amount of XLM to OSW account {0} as a donation', ExternalLink::getStellarExpertAccountFullLink(StellarGiver::getGiverPublicKey())) ?>. <?= Yii::t('bot', 'When we help one another, everybody wins') ?>. <?= Yii::t('bot', 'Pay it forward') ?>!<br/>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$statusActiveUrl = Yii::$app->urlManager->createUrl(['stellar-basic-income/set-active']);
$statusInactiveUrl = Yii::$app->urlManager->createUrl(['stellar-basic-income/set-inactive']);

$script = <<<JS
$('.status-update').on("click", function(event) {
    const status = $(this).data('value');
    const active_url = '{$statusActiveUrl}';
    const inactive_url = '{$statusInactiveUrl}';
    const url = (parseInt(status) === 1) ? active_url : inactive_url;

        $.post(url, {}, function(result) {
            if (result === true) {
                location.reload();
            }
            else {

                $('#main-modal-header').text('Warning!');

                for (const [, errorMsg] of Object.entries(result)) {
                    $('#main-modal-body').append('<p>' + errorMsg + '</p>');
                }

                $('#main-modal').show();
                $('.close').on('click', function() {
                    $("#main-modal-body").html("");
                    $('#main-modal').hide();
                });
            }
        });

    return false;
});
JS;
$this->registerJs($script);
