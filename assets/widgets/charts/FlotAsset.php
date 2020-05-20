<?php

namespace app\assets\widgets\charts;

use yii\web\AssetBundle;


class FlotAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/jquery.flot@0.8.3/jquery.flot.js',
        'https://cdn.jsdelivr.net/npm/jquery.flot@0.8.3/jquery.flot.resize.js',
        'https://cdn.jsdelivr.net/npm/jquery.flot@0.8.3/jquery.flot.pie.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
