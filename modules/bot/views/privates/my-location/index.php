<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<b><?= Yii::t('bot', 'Your Location') ?></b><br/>
<br/>
<a href="<?= ExternalLink::getOSMLink($userLocation->location_lat, $userLocation->location_lon) ?>"><?= $userLocation->location ?></a><br/>
<br/>
<i><?= Yii::t('bot', 'This information is used for all services') ?>.</i>
