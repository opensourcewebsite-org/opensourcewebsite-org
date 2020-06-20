<?php

use app\components\helpers\ArrayHelper;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\AdSearch;
use app\modules\bot\models\AdSection;

/** @var AdSearch $model */

$keywords = ArrayHelper::getColumn($model->getKeywords()->all(), 'keyword');
$locationLink = ExternalLink::getOSMLink(
    $model->location_lat,
    $model->location_lon
);
$liveDays = AdSearch::LIVE_DAYS;
$showDetailedInfo = true;
$currency = $model->currencyRelation;

?>
🔍<b><?= AdSection::getAdSearchName($model->section) ?></b> - <b><?= $model->title ?></b><br/>
<br/>
<?php
if ($model->description !== null) : ?>
    <?= nl2br($model->description); ?><br/>
    <br/>
<?php
endif; ?>
<?php
if ($keywords) : ?>
    # <i><?= implode(', ', $keywords); ?></i><br/>
    <br/>
<?php
endif; ?>
<?php
if ($model->currency_id !== null && $model->max_price !== null) : ?>
    <b><?= Yii::t('bot', 'Max price') ?>:</b> <?= $model->max_price ?> <?= $currency->code ?><br/>
    <br/>
<?php
endif; ?>
<b><?= Yii::t('bot', 'Location') ?>:</b> <a
    href="<?= $locationLink ?>"><?= $model->location_lat ?> <?= $model->location_lon ?></a><br/>
<br/>
<?php
if ($model->pickup_radius > 0) : ?>
    <b><?= Yii::t('bot', 'Pickup radius') ?>:</b> <?= $model->pickup_radius ?> <?= Yii::t('bot', 'km') ?><br/>
    <br/>
<?php
endif; ?>
<?php
if ($model->isActive() && $showDetailedInfo) : ?>
    <i><?= Yii::t('bot', 'You will receive a notification in case of matches with offers of other users') ?>
        . <?= Yii::t('bot', 'This page is active for {0,number} more days', $liveDays) ?>. <?= Yii::t(
            'bot',
            'Visit this page again before this term to automatically renew this'
        ) ?>.</i>
<?php
endif; ?>
