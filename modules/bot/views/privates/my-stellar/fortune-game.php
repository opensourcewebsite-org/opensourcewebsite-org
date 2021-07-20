<?php

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;

$croupier = Yii::$app->params['stellar']['croupier_public_key'] ?? null;

?>
<b><?= Yii::t('bot', 'Fortune Game') ?></b><br/>
<br/>
<?php if ($croupier) : ?>
<?= Yii::t('bot', 'Send any amount of XLM to OSW account {0} and check your luck', ExternalLink::getStellarExpertAccountFullLink($croupier)) ?>. <?= Yii::t('bot', 'Minimum amount {0} XLM', '0.001') ?>.<br/>
<br/>
<?php endif; ?>
<i><?= Yii::t('bot', 'If you have any suggestions, questions or feedback, please contact our team') ?>: @opensourcewebsite</i>
