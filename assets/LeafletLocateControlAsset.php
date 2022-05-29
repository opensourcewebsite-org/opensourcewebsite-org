<?php

namespace app\assets;

use yii\web\AssetBundle;

class LeafletLocateControlAsset extends AssetBundle
{
    public $sourcePath = '@npm/leaflet.locatecontrol';

    public $css = [
        'dist/L.Control.Locate.min.css',
    ];

    public $js = [
        'dist/L.Control.Locate.min.js',
    ];

    public $depends = [
        LeafLetAsset::class,
    ];
}
