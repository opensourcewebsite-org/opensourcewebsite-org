<?php
/**
 * Application requirement checker script.
 *
 * In order to run this script use the following console command:
 * php requirements.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

if (file_exists('.env')) {
    $dotenv = new Dotenv(__DIR__);
    $dotenv->load();
} elseif (file_exists('.env.test')) {
    $dotenv = new Dotenv(__DIR__, '.env.test');
    $dotenv->load();
} else {
    exit;
}

defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'requirements');

if (YII_ENV != 'dev') {
    exit;
}

require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/requirements/YiiRequirementChecker.php';

$requirementsChecker = new YiiRequirementChecker();

$gdMemo = $imagickMemo = 'Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required for image CAPTCHA.';
$gdOK = $imagickOK = false;

if (extension_loaded('imagick')) {
    $imagick = new Imagick();
    $imagickFormats = $imagick->queryFormats('PNG');
    if (in_array('PNG', $imagickFormats)) {
        $imagickOK = true;
    } else {
        $imagickMemo = 'Imagick extension should be installed with PNG support in order to be used for image CAPTCHA.';
    }
}

if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    if (!empty($gdInfo['FreeType Support'])) {
        $gdOK = true;
    } else {
        $gdMemo = 'GD extension should be installed with FreeType support in order to be used for image CAPTCHA.';
    }
}

switch (Yii::$app->db->driverName) {
    case 'mysql':
        $dbVersionOk = version_compare('5.6.0', '8.0.0', '>=') ? true : false; //Yii::$app->db->getServerVersion(), '8.0.0', '>=') ? true : false;
        break;
    default:
        $dbVersionOk = false;
        break;
}

/**
 * Adjust requirements according to your application specifics.
 */
$requirements = [
    // Database :
    [
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes',
    ],
    [
        'name' => 'PDO MySQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => '<a href="https://www.php.net/manual/en/ref.pdo-mysql.php">PDO MySQL extension</a>',
        'memo' => 'Required for MySQL database.',
    ],
    [
        'name' => 'MySQL server version',
        'mandatory' => true,
        'condition' => $dbVersionOk,
        'by' => 'Checking DB version',
        'memo' => 'MySQL checking database version',
    ],
    // CAPTCHA:
    [
        'name' => 'GD PHP extension with FreeType support',
        'mandatory' => false,
        'condition' => $gdOK,
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-captcha-captcha.html">Captcha</a>',
        'memo' => $gdMemo,
    ],
    [
        'name' => 'ImageMagick PHP extension with PNG support',
        'mandatory' => false,
        'condition' => $imagickOK,
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-captcha-captcha.html">Captcha</a>',
        'memo' => $imagickMemo,
    ],
    // JSON
    [
        'name' => 'JSON extension',
        'mandatory' => true,
        'condition' => extension_loaded('json'),
        'by' => '<a href="https://www.php.net/manual/en/book.json.php">JSON extension</a>'
    ],
    // PHP ini :
    'phpExposePhp' => [
        'name' => 'Expose PHP',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
        'by' => 'Security reasons',
        'memo' => '"expose_php" should be disabled at php.ini',
    ],
    'phpAllowUrlInclude' => [
        'name' => 'PHP allow url include',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
        'by' => 'Security reasons',
        'memo' => '"allow_url_include" should be disabled at php.ini',
    ],
    'phpSmtp' => [
        'name' => 'PHP mail SMTP',
        'mandatory' => false,
        'condition' => strlen(ini_get('SMTP')) > 0,
        'by' => 'Email sending',
        'memo' => 'PHP mail SMTP server required',
    ],
];

// OPcache check
if (!version_compare(phpversion(), '7.2', '>=')) {
    $requirements[] = [
        'name' => 'APC extension',
        'mandatory' => false,
        'condition' => extension_loaded('apc'),
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-caching-apccache.html">ApcCache</a>',
    ];
}

$requirementsChecker->checkYii()->check($requirements)->render();
