<?php

namespace app\assets\widgets;

use yii\web\AssetBundle;


class CommonAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',
        'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/all.min.css',
    ];

    public $js = [
        'https://code.jquery.com/jquery-3.5.1.min.js',
        'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',
        'dist/js/demo.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
