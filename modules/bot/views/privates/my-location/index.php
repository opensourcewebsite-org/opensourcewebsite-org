<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= Yii::t('bot', 'Your Location') ?></b><br/>
<?php if (!$userLocation->isNewRecord) : ?>
<br/>
<?= ExternalLink::getOSMFullLink($userLocation->location_lat, $userLocation->location_lon) ?><br/>
<?php endif; ?>
————<br/>
<i><?= Yii::t('bot', 'This information is used for all services') ?>. <?= Yii::t('bot', 'Only you see this information') ?>.</i><br/>
<br/>
<?= Emoji::REFRESH ?> <i><?= Yii::t('bot', 'Send a location using app feature or type it in format «Latitude Longitude»') ?></i>.
