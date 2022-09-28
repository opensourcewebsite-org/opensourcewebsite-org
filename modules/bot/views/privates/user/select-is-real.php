<?php

use app\models\Contact;

?>
<b><?= Yii::t('bot', 'Choose a value') ?>:</b><br/>
<br/>
<i><?= Yii::t('bot', 'Only you see this information') ?>.</i><br/>
<br/>
<i><?= Yii::t('bot', 'If you select «{0}» then this user will receive a notification', Contact::getIsRealLabels()[1]) ?>.</i><br/>
