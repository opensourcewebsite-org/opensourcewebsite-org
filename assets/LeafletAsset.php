<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Leaflet Asset
 *
 * @link https://cdnjs.com/libraries/leaflet
 * @link https://leafletjs.com
 */
class LeafletAsset extends AssetBundle
{
    public $css = [
        '//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet.min.css',
    ];

    public $js = [
        '//cdnjs.cloudflare.com/ajax/libs/leaflet/1.8.0/leaflet-src.min.js',
    ];
}
