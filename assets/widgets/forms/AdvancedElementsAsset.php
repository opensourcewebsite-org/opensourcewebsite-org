<?php

namespace app\assets\widgets\forms;

use yii\web\AssetBundle;

class AdvancedElementsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.2.0/dist/css/bootstrap-colorpicker.min.css',
        'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css',
        'https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap4-duallistbox@4.0.2/dist/bootstrap-duallistbox.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap4-duallistbox@4.0.2/dist/jquery.bootstrap-duallistbox.min.js',
        'https://cdn.jsdelivr.net/npm/jquery.inputmask@3.3.4/dist/jquery.inputmask.bundle.js',
        'https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.2.0/dist/js/bootstrap-colorpicker.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
