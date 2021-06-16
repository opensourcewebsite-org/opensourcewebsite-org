<?php

use app\modules\bot\components\helpers\ExternalLink;

?>
<?= Yii::t('bot', 'Formatting options') ?>:<br/>
<br/>
  <code>**<?= Yii::t('bot', 'bold') ?>**</code> => <b><?= Yii::t('bot', 'bold') ?></b><br/>
  <code>__<?= Yii::t('bot', 'italic') ?>__</code> => <i><?= Yii::t('bot', 'italic') ?></i><br/>
  <code>~~<?= Yii::t('bot', 'strike') ?>~~</code> => <s><?= Yii::t('bot', 'strike') ?></s><br/>
  <code>`<?= Yii::t('bot', 'code') ?>`</code> => <code><?= Yii::t('bot', 'code') ?></code><br/>
  <code>[<?= Yii::t('bot', 'link title') ?>](<?= Yii::t('bot', 'link') ?>)</code> => <a href="<?= ExternalLink::getBotLink() ?>"><?= Yii::t('bot', 'link title') ?></a><br/>
<br/>
<i><?= Yii::t('bot', 'HTML tags are ignored') ?>.</i><br/>
