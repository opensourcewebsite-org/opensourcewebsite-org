<?php
use app\models\Currency;

$controller = Yii::$app->controller;
$currency_id = $controller->field->get($controller->modelName, 'buying_currency_id');
$currency = Currency::findOne($currency_id);

?>

<b><?= Yii::t('bot', 'Choose a buying currency or type it') ?></b>
<?php if (isset(Yii::$app->controller->rule['isVirtual']) && isset($currency_id)) : ?>
<b>, <?= Yii::t('bot', 'or click NEXT to use existing currency') ?> </b>
<b><?= $currency->code ?></b>
<?php endif; ?>
: