<?php

namespace app\components;

use TelegramBot\Api\Types\Message;
use yii\base\Controller;
use app\models\BotClient;

/**
 * Class BotCommandController
 *
 * @package app\components
 */
class BotCommandController extends Controller
{

    /**
     * @var bool|null|string
     */
    public $layout = false;

    /**
     * @var string the root directory that contains view files for this controller.
     */
    protected $_viewPath;

    /**
     * @var Message
     */
    public $requestMessage = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_ACTION, [$this, 'onBeforeAction']);
    }

    /**
     * set language
     */
    public function onBeforeAction(/* $event */)
    {
        if ($this->requestMessage && $clientData = $this->requestMessage->getFrom()) {
            if ($botClient = BotClient::findOne(['provider_user_id' => $clientData->getId()])) {
                \Yii::$app->language = $botClient->language_code;
            }
        }
    }

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

    /**
     * Returns the directory containing view files for this controller.
     * The default implementation returns the directory named as controller [[id]] under the [[module]]'s
     * [[viewPath]] directory.
     *
     * @return string the directory containing the view files for this controller.
     */
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->module->getViewPath() . DIRECTORY_SEPARATOR . 'bot' . DIRECTORY_SEPARATOR . $this->id;
        }

        return $this->_viewPath;
    }
}