<?php

namespace app\components;

use yii\base\Component;
use yii\base\InvalidRouteException;

/**
 * Class BotCommandRouter
 *
 * @package app\components
 */
class BotCommandRouter extends Component
{
    /**
     * @var string
     */
    public $defaultAction = 'index';

    /**
     * @var array
     */
    public $controllerMap = [];

    /**
     * @var string
     */
    public $controllerNamespace = 'app\\controllers\\bot';

    /**
     * @param $commandString
     * @param $requestMessage
     *
     * @return mixed
     * @throws InvalidRouteException
     * @throws \yii\base\InvalidConfigException
     */
    public function dispatchCommand($commandString, $requestMessage)
    {
        $parts = $this->createController($commandString);
        if (is_array($parts) && is_object($parts['controller'])) {
            /* @var $controller \app\components\BotCommandController */
            $controller = $parts['controller'];
            $controller->requestMessage = $requestMessage;

            return $controller->runAction($parts['actionID'], $parts['params']);
        }

        throw new InvalidRouteException('Unable to resolve the command "' . $commandString . '".');
    }

    /**
     * @param $commandString
     *
     * @return array
     */
    public function parseRoute($commandString)
    {
        $parts = ['controller' => '', 'actionID' => $this->defaultAction, 'params' => []];

        $commandString = substr($commandString, 1);
        $stringParts = explode(' ', $commandString);
        $controllerID = array_shift($stringParts);
        $parts['id'] = $controllerID;
        $controllerID = implode('', array_map('ucfirst', explode('-', $controllerID)));
        $controllerID .= 'Controller';

        $parts['controller'] = $controllerID;
        $parts['params'] = $stringParts;

        return $parts;
    }

    /**
     * @param $commandString
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function createController($commandString)
    {
        $parts = $this->parseRoute($commandString);
        $controllerID = $parts['id'];
        if (isset($this->controllerMap[$controllerID])) {
            $parts['controller'] = \Yii::createObject($this->controllerMap[$controllerID], [$controllerID, \Yii::$app]);
        } else {
            $controllerClass = $this->controllerNamespace . '\\' . $parts['controller'];
            if ($this->isCorrectClassName($controllerClass)) {
                $parts['controller'] = \Yii::createObject($controllerClass, [$controllerID, \Yii::$app]);
            }
        }

        return $parts;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function isCorrectClassName($className)
    {
        $result = true;
        if (!preg_match('%^[a-z][a-z0-9\\\-_]*$%i', $className)) {
            $result = false;
        }
        if (!class_exists($className)) {
            $result = false;
        }

        return $result;
    }
}