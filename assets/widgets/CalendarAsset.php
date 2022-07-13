<?php

namespace app\assets\widgets;

use yii\web\AssetBundle;

class CalendarAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.css',
        '//cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css',
        '//cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css',
        '//cdn.jsdelivr.net/npm/@fullcalendar/bootstrap@4.4.0/main.min.css',
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
        '//cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.js',
        '//cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js',
        '//cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js',
        '//cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js',
        '//cdn.jsdelivr.net/npm/@fullcalendar/bootstrap@4.4.0/main.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
