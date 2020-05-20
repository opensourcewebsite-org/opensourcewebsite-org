<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;

class SweetAlertAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4@3.1.4/bootstrap-4.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/sweetalert2@9.10.13/dist/sweetalert2.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
