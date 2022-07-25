<?php

namespace app\assets\widgets\forms;

use yii\web\AssetBundle;

class AdvancedElementsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.2.0/dist/css/bootstrap-colorpicker.min.css',
        '//cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css',
        '//cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css',
        '//cdn.jsdelivr.net/npm/bootstrap4-duallistbox@4.0.2/dist/bootstrap-duallistbox.min.css',
        '//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css',
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js',
        '//cdn.jsdelivr.net/npm/bootstrap4-duallistbox@4.0.2/dist/jquery.bootstrap-duallistbox.min.js',
        '//cdn.jsdelivr.net/npm/jquery.inputmask@3.3.4/dist/jquery.inputmask.bundle.js',
        '//cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.2.0/dist/js/bootstrap-colorpicker.min.js',
        '//cdn.jsdelivr.net/npm/bootstrap-switch@3.4.0/dist/js/bootstrap-switch.min.js',
        '//cdn.jsdelivr.net/momentjs/latest/moment.min.js',
        '//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        //'app\assets\widgets\CommonAsset',
    ];
}
