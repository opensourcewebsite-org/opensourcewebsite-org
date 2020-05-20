<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;

class SlidersAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdn.jsdelivr.net/npm/ion-rangeslider@2.3.1/css/ion.rangeSlider.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap-slider@10.6.2/dist/css/bootstrap-slider.min.css',
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/ion-rangeslider@2.3.1/js/ion.rangeSlider.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap-slider@10.6.2/dist/bootstrap-slider.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
