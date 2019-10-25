<?php

namespace app\components;

use yii\base\Controller;

/**
 * Class BotCommandController
 *
 * @package app\components
 */
class BotCommandController extends Controller
{

    /**
     * @var null
     */
    public $requestMessage = null;

    /**
     * @param \yii\base\Action $action
     * @param array $params
     *
     * @return array
     */
    public function bindActionParams($action, $params)
    {
        return ['params' => $params];
    }
}