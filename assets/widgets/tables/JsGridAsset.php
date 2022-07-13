<?php

namespace app\assets\widgets\tables;

use yii\web\AssetBundle;

class JsGridAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//cdn.jsdelivr.net/npm/jsgrid@1.5.3/css/jsgrid.css',
        '//cdn.jsdelivr.net/npm/jsgrid@1.5.3/css/theme.css',
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/jsgrid@1.5.3/demos/db.js',
        '//cdn.jsdelivr.net/npm/jsgrid@1.5.3/dist/jsgrid.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
