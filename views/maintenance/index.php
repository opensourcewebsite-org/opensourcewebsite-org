<?php

use yii\helpers\Html;

?>
<div class="row">
    <div class="col-md-12 text-center">
        <i class="fa fa-cogs" style="font-size: 72px; margin-top: 1.5em;"></i>
        <h2 style="margin: 20px 0;">We&rsquo;ll be back online shortly!</h2>
        <p>Our Website is down for scheduled maintenance.<br>Shouldn't be long. Check back soon.</p>
    </div>
</div>
<!--
<h2>We&rsquo;ll be back soon!</h2>
<div>
    <p>
        <?php if (Yii::$app->maintenanceMode->message): ?>

            <?php echo Yii::$app->maintenanceMode->message; ?>

        <?php else: ?>

            Sorry for the inconvenience but we’re performing some maintenance at the moment.
            If you need to you can always <?= Html::mailto('contact us', (\Yii::$app->params['adminEmail'] ? \Yii::$app->params['adminEmail'] : '#')) ?>,
            otherwise we’ll be back online shortly!

        <?php endif; ?>
    </p>
</div>
-->
