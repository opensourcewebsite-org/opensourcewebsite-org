<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\modules\comment\assets;

use yii\web\AssetBundle;

/**
 * Class CommentsAsset
 *
 * @package app\components\comments
 */
class CommentsAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/comment/assets';
    public $js = [
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
