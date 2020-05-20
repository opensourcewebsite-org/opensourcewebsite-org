<?php

namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;


class VisitorsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css',
        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.usa.js',
        'https://cdn.jsdelivr.net/npm/jquery-knob@1.2.11/dist/jquery.knob.min.js',
        'https://cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
