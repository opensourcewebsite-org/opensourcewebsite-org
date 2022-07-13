<?php

namespace app\assets\widgets\tables;

use yii\web\AssetBundle;

class DataTablesAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        '//cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css',
    ];

    public $js = [
        '//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js',
        '//cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
