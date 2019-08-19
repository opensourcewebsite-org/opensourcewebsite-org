<?php

use Dotenv\Dotenv;

$config = include '../config/web-local.php';

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv('..');
$dotenv->load();

defined('YII_DEBUG') or define('YII_DEBUG', (getenv('YII_DEBUG') == 'true') ? true : false);
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::$classMap['yii\helpers\Html'] = '@app/components/Html.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
