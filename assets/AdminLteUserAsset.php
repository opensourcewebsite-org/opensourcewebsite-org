<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * AdminLteUserAsset asset bundle.
 */
class AdminLteUserAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/site.css',
        'css/adminlte-fix.css',
    ];

    public $js = [
    ];

	public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
