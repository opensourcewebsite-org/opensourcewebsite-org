<?php

namespace app\assets\widgets\charts;

use yii\web\AssetBundle;

class InlineAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/jquery-knob@1.2.11/dist/jquery.knob.min.js',
        '//cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
