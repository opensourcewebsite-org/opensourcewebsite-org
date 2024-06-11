<?php

namespace app\modules\api;

use Yii;

/**
 * OSW API module definition class
 *
 * @link https://opensourcewebsite.org/api
 * @link https://apidocs.opensourcewebsite.org
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Yii::configure($this, require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'common.php');

        $handler = $this->get('errorHandler');

        Yii::$app->set('errorHandler', $handler);

        $handler->register();
    }
}
