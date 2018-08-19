<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Awesome font css asset
 */
class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/npm-asset/font-awesome/web-fonts-with-css';
    public $css = [
        'css/fontawesome-all.min.css',
    ];
}
