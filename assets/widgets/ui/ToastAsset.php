<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;


class ToastAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css',
    ];

    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
