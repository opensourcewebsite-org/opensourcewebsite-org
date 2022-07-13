<?php

namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;

class SalesGraphAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',
        '//cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js',
        '//cdn.jsdelivr.net/npm/jquery-knob@1.2.11/dist/jquery.knob.min.js',
        '//cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
        '//cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
