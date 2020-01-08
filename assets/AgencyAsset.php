<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Agency asset bundle.
 */
class AgencyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/agency.css',
        'css/agency-fix.css',
    ];
    public $js = [
        'js/agency.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\JqueryAsset',
    ];
}
