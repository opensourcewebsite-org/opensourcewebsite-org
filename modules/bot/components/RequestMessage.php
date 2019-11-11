<?php

namespace app\modules\bot\components;

use app\modules\bot\telegram\Message;
use yii\base\Component;


/**
 * Class RequestMessage
 *
 * @package app\modules\bot\components
 */
class RequestMessage extends Component
{
    /** @var Message */
    protected $_message;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->_message = new Message();
    }

    /**
     * @param Message $message
     */
    public function setMessage($message)
    {
        if ($message) {
            $this->_message = $message;
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_message) ? true : parent::__isset($name);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        $result = null;
        if (isset($this->_message->{$name})) {
            $result = $this->_message->{$name};
        } else {
            $result = parent::__get($name);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if (isset($this->_message->{$name})) {
            $this->_message->{$name} = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @return mixed
     */
    public function __call($name, $params)
    {
        if (method_exists($this->_message, $name)) {
            return call_user_func_array([$this->_message, $name], $params);
        }

        return parent::__call($name, $params);
    }
}