<?php

namespace app\assets\widgets;

use yii\web\AssetBundle;

class Dashboard1Asset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css',
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',
        'plugins/fontawesome-free/css/all.min.css',

        'https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/css/tempusdominus-bootstrap-4.min.css',
        'https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css',
        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/css/OverlayScrollbars.min.css',
        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.css',
        'https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.css',
    ];

    public $js = [
        'https://code.jquery.com/jquery-3.5.1.min.js',
        'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js',

        'https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',
        'https://cdn.jsdelivr.net/npm/jquery-sparkline@2.4.0/jquery.sparkline.min.js',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js',
        'https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.usa.js',
        'https://cdn.jsdelivr.net/npm/jquery-knob@1.2.11/dist/jquery.knob.min.js',
        'https://cdn.jsdelivr.net/npm/moment@2.25.3/moment.min.js',
        'https://cdn.jsdelivr.net/npm/daterangepicker@3.0.5/daterangepicker.js',
        'https://cdn.jsdelivr.net/npm/tempusdominus-bootstrap-4@5.1.2/build/js/tempusdominus-bootstrap-4.min.js',
        'https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.js',
        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/js/jquery.overlayScrollbars.min.js',
        'dist/js/demo.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
