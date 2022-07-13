<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;

class RibbonsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        '//cdn.jsdelivr.net/npm/ion-rangeslider@2.3.1/js/ion.rangeSlider.min.js',
        '//cdn.jsdelivr.net/npm/bootstrap-slider@10.6.2/dist/bootstrap-slider.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
