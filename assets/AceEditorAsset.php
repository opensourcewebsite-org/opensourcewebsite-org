<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Ace Editor asset
 */
class AceEditorAsset extends AssetBundle
{
    public $sourcePath = '@vendor/npm-asset/ace-builds';

    public $js = [
        'src-min-noconflict/ace.js',
        'src-min-noconflict/ext-emmet.js',
    ];

    public $depends = [
        'app\assets\AceEditorStyleAsset',
        'app\assets\EmmetAsset',
    ];
}
