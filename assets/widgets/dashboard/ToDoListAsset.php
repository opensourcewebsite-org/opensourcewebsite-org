<?php

 namespace app\assets\widgets\dashboard;

use yii\web\AssetBundle;


class ToDoListAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/css/OverlayScrollbars.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/overlayscrollbars@1.12.0/js/jquery.overlayScrollbars.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
