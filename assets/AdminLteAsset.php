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
      'bootstrap/css/bootstrap.min.css',
      'dist/css/adminlte.min.css',
    ];
    public $js = [
    	'bootstrap/js/bootstrap.min.js',
    	'dist/js/adminlte.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
