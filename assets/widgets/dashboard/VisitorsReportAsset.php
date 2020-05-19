<?php

 namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;


class VisitorsReportAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/raphael@2.3.0/raphael.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/js/jquery.mapael.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/js/maps/usa_states.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
