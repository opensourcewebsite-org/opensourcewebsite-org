<?php
use app\models\Currency;

$controller = Yii::$app->controller;
$currency_id = $controller->field->get($controller->modelName, 'selling_currency_id');
$currency = Currency::findOne($currency_id);

?>

<b><?= Yii::t('bot', 'Choose a selling currency or type it') ?></b>
<?php if (isset(Yii::$app->controller->rule['isVirtual']) && isset($currency_id)) : ?>
<b>, <?= Yii::t('bot', 'or click NEXT to use existing currency') ?> </b>
<b><?= $currency->code ?></b>
<?php endif; ?>
: