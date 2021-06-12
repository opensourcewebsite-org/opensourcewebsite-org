<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Yii::t('bot', 'Formatting options') ?>:<br/>
  <b><?= Yii::t('bot', 'bold') ?></b> => <code>**<?= Yii::t('bot', 'bold') ?>**</code><br/>
  <i><?= Yii::t('bot', 'italic') ?></i> => <code>__<?= Yii::t('bot', 'italic') ?>__</code><br/>
  <s><?= Yii::t('bot', 'strike') ?></s> => <code>~~<?= Yii::t('bot', 'strike') ?>~~</code><br/>
  <code><?= Yii::t('bot', 'code') ?></code> => <code>`<?= Yii::t('bot', 'code') ?>`</code><br/>
  <a href="<?= ExternalLink::getBotLink() ?>"><?= Yii::t('bot', 'link title') ?></a> => <code>[<?= Yii::t('bot', 'link title') ?>](<?= Yii::t('bot', 'link') ?>)</code><br/>
<br/>
