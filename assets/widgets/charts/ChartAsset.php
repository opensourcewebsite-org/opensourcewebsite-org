<?php

namespace app\assets\widgets\charts;

use yii\web\AssetBundle;
use yii\web\View;


class ChartAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
        'https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
