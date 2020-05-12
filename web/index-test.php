<?php

use Dotenv\Dotenv;

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}
require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__, '../.env.test');
$dotenv->load();

defined('YII_DEBUG') or define('YII_DEBUG', (getenv('YII_DEBUG') == 'true') ? true : false);
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'test');

require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/test.php';

(new yii\web\Application($config))->run();
