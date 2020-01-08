<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * AceEditor Css Asset
 */
class AceEditorStyleAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/ace-editor.css',
    ];
}
