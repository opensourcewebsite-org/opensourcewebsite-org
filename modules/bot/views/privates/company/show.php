<b><?= $name ?></b><br/>
<br/>
<?php
if ($description):
    ?>
    <?= nl2br($description) ?><br/>
    <br/>
<?php
endif;
if ($address):
    ?>
    <?= Yii::t('bot', 'Address') ?>: <?= $address ?><br/>
    <br/>
<?php
endif;
if ($url):
    ?>
    <?= Yii::t('bot', 'Website') ?>: <?= $url ?><br/>
    <br/>
<?php
endif;
