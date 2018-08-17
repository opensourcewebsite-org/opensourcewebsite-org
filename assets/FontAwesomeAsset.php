<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Awesome font css asset
 */
class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/fortawesome/font-awesome';
    public $css = [
        'css/font-awesome.css',
    ];
}
