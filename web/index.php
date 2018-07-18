<?php

$debug = false;

$config = include '../config/web-local.php';

defined('YII_DEBUG') or define('YII_DEBUG', $debug);
defined('YII_ENV') or define('YII_ENV', $debug ? 'dev' : 'prod');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::$classMap['yii\helpers\Html'] = '@app/components/Html.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
