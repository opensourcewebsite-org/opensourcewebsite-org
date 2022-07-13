<?php

namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;

class Dashboard3Asset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
        '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',
        '//code.jquery.com/jquery-3.5.1.min.js',
        '//code.jquery.com/ui/1.12.1/jquery-ui.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\DemoAsset',
    ];
}
