<?php

namespace app\assets\widgets\forms;

use yii\web\AssetBundle;

class EditorsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte';

    public $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.20.0/ui/trumbowyg.min.css',
        'https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.css',
    ];

    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.20.0/trumbowyg.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'app\assets\widgets\CommonAsset',
    ];
}
