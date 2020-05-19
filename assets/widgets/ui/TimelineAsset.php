<?php

namespace app\assets\widgets\ui;

use yii\web\AssetBundle;


class TimelineAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
