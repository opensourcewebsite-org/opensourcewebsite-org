<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;

class ModalsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',
        'plugins/fontawesome-free/css/all.min.css',
        'plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
        'plugins/toastr/toastr.min.css',
    ];

    public $js = [
        'plugins/sweetalert2/sweetalert2.min.js',
        'plugins/toastr/toastr.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
