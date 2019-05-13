<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Emmet.js Asset
 */
class EmmetAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/emmet-core/emmet.js',
    ];
}
