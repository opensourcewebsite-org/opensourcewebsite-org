<?php

namespace app\modules\bot\services;

use app\modules\bot\components\Controller;
use app\modules\bot\components\CrudController;
use app\modules\bot\controllers\privates\MenuController;

/**
 * Class AttributeButtonsService
 *
 * @package app\modules\bot\services
 */
class AttributeButtonsService
{
    /** @var Controller */
    public $controller;

    /**
     * @param array $rule
     * @param string $attributeName
     *
     * @return array
     */
    public function get($rule, $attributeName)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['buttons'] ?? []) {
            $buttons = $this->fillButtonsCallbackData($configButtons, $attributeName);
        }

        return $buttons;
    }

    /**
     * @param array $rule
     * @param string $attributeName
     *
     * @return array
     */
    public function getSystems($rule, $attributeName)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['systemButtons'] ?? []) {
            $buttons = $this->fillButtonsCallbackData($configButtons, $attributeName);
        }

        return $buttons;
    }

    /**
     * @param $configButtons
     * @param $attributeName
     *
     * @return array
     */
    private function fillButtonsCallbackData(&$configButtons, $attributeName)
    {
        $buttons = [];
        foreach ($configButtons as $key => $configButton) {
            $configButton['callback_data'] = $this->getButtonRoute($configButton, $key, $attributeName);
            $buttons[$key] = $configButton;
        }

        return $buttons;
    }

    /**
     * @param string $attributeName
     * @param array $rule
     *
     * @return bool
     */
    public function isPrivateAttribute(string $attributeName, array $rule)
    {
        $config = $rule['attributes'][$attributeName];

        return array_key_exists('relation', $config);
    }

    /**
     * @param $configButton
     * @param $id
     * @param $attributeName
     *
     * @return string
     */
    private function getButtonRoute(&$configButton, $id, $attributeName)
    {
        if (isset($configButton['item'])) {
            $route = $this->controller::createRoute(
                'sh-a',
                [
                    'a' => $configButton['item'],
                ]
            );
            unset($configButton['item']);
        } elseif (isset($configButton['route'])) {
            $route = $configButton['route'];
            unset($configButton['route']);
        } elseif (isset($configButton['callback'])) {
            $route = $this->controller::createRoute('b-c', ['a' => $attributeName, 'i' => $id]);
            unset($configButton['callback']);
        } else {
            $route = MenuController::createRoute();
        }

        return $route;
    }
}
