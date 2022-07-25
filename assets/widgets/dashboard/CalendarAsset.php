<?php

namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;

class CalendarAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/css/tempusdominus-bootstrap-4.min.css',
        '//cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.css',
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
        '//cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/js/tempusdominus-bootstrap-4.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
