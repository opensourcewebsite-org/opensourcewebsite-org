<?php
namespace app\assets;

use yii\web\AssetBundle;

/**
 * AdminLte asset bundle.
 */
class AdminLteGuestAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';
    public $css = [
      'dist/css/adminlte.min.css',
      'plugins/font-awesome/css/font-awesome.min.css',
    ];
    public $js = [
        'dist/js/adminlte.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
