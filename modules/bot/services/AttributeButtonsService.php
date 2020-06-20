<?php

namespace app\modules\bot\services;

use app\modules\bot\components\Controller;
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
     * @param array  $rule
     * @param string $attributeName
     *
     * @return array
     */
    public function get($rule, $attributeName)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['buttons'] ?? []) {
            foreach ($configButtons as $configButton) {
                $configButton['callback_data'] = $this->getButtonRoute($configButton);
                $buttons[] = $configButton;
            }
        }

        return $buttons;
    }

    /**
     * @param array  $rule
     * @param string $attributeName
     *
     * @return array
     */
    public function getSystems($rule, $attributeName)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['systemButtons'] ?? []) {
            foreach ($configButtons as $key => $configButton) {
                $configButton['callback_data'] = $this->getButtonRoute($configButton);
                $buttons[$key] = $configButton;
            }
        }

        return $buttons;
    }

    /**
     * @param string $attributeName
     * @param array  $rule
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
     *
     * @return string
     */
    private function getButtonRoute(&$configButton)
    {
        if (isset($configButton['item'])) {
            $route = $this->controller::createRoute(
                'sh-a',
                [
                    'a' => $configButton['item'],
                ]
            );
            unset($configButton['item']);
        } elseif ($configButton['route']) {
            $route = $configButton['route'];
            unset($configButton['route']);
        } else {
            $route = MenuController::createRoute();
        }

        return $route;
    }
}
