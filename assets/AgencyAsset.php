<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Agency asset bundle.
 *
 * @link https://startbootstrap.com/template-overviews/agency
 * @link https://cdnjs.com/libraries/startbootstrap-agency
 */
class AgencyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        '//cdnjs.cloudflare.com/ajax/libs/startbootstrap-agency/5.2.2/css/agency.min.css',
        'css/agency-fix.css',
    ];

    public $js = [
        '//cdnjs.cloudflare.com/ajax/libs/startbootstrap-agency/5.2.2/js/agency.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'yii\web\JqueryAsset',
    ];
}
