<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * AdminLte asset bundle.
 */
class AdminLteAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';
    public $css = [
        'dist/css/adminlte.min.css',
        '/css/site.css',
        '/css/adminlte-fix.css',
    ];
    public $js = [
        'dist/js/adminlte.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
