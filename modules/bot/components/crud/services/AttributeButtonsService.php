<?php

namespace app\modules\bot\components\crud\services;

use app\components\helpers\ArrayHelper;
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
     *  'attribute_name' => [
     *      'buttons' => [
     *      [
     *          'hideCondition' => true,
     *          'createMode' => false, //default is true
     *          'editMode' => false, //default is true
     *          'text' => Yii::t('bot', 'Edit attribute'),
     *          //you can you one
     *          'item' => 'other_attribute_name',
     *          //OR
     *          'route' => MenuController::createRoute(),
     *          //OR
     *          'callback' => function (ActiveRecord $model) {
     *              $model->attribute = 0;
     *              return $model;
     *          },
     *      ],
     *  ],
     *
     * @param array $rule
     * @param string $attributeName
     * @param integer|null $modelId
     *
     * @return array
     */
    public function get($rule, $attributeName, $modelId)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['buttons'] ?? []) {
            $buttons = $this->fillButtonsCallbackData(
                $configButtons,
                $attributeName,
                compact('modelId', 'rule')
            );
        }

        return $buttons;
    }

    /**
     *  'attribute_name' => [
     *      'systemButtons' => [
     *          'back' => [
     *              'hideCondition' => true,
     *              'createMode' => false, //default is true
     *              'editMode' => false, //default is true
     *              'text' => Yii::t('bot', 'Edit attribute'),
     *              //you can you one
     *              'item' => 'other_attribute_name',
     *              //OR
     *              'route' => MenuController::createRoute(),
     *              //OR
     *              'callback' => function (ActiveRecord $model) {
     *                  $model->attribute = 0;
     *                  return $model;
     *              },
     *          ],
     *      ],
     *  ]
     *
     * @param array $rule
     * @param string $attributeName
     * @param integer|null $modelId
     *
     * @return array
     */
    public function getSystems($rule, $attributeName, $modelId)
    {
        $buttons = [];
        $config = $rule['attributes'][$attributeName];
        if ($configButtons = $config['systemButtons'] ?? []) {
            $buttons = $this->fillButtonsCallbackData(
                $configButtons,
                $attributeName,
                compact('modelId', 'rule')
            );
        }

        return $buttons;
    }

    /**
     * @param $configButtons
     * @param $attributeName
     * @param array $options
     *
     * @return array
     */
    private function fillButtonsCallbackData(&$configButtons, $attributeName, $options)
    {
        $modelId = ArrayHelper::getValue($options, 'modelId', null);
        $rule = ArrayHelper::getValue($options, 'rule', []);
        $buttons = [];
        foreach ($configButtons as $key => $configButton) {
            if (isset($configButton['hideCondition']) && $configButton['hideCondition']) {
                continue;
            }
            if (($modelId && !($configButton['editMode'] ?? true))
                || (!$modelId && !($configButton['createMode'] ?? true))) {
                continue;
            }
            $configButton['callback_data'] = $this->getButtonRoute(
                $configButton,
                $key,
                compact('attributeName', 'modelId', 'rule')
            );
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
     * @param array $configButton
     * @param integer $buttonKey
     * @param array $options
     *
     * @return string
     */
    private function getButtonRoute(&$configButton, $buttonKey, $options = [])
    {
        $attributeName = ArrayHelper::getValue($options, 'attributeName', null);
        $id = ArrayHelper::getValue($options, 'modelId', null);
        $rule = ArrayHelper::getValue($options, 'rule', []);
        if (isset($configButton['item'])) {
            $route = $this->createAttributeRoute(
                $this->controller->getModelName($rule['model']),
                $configButton['item'],
                $id
            );
            unset($configButton['item']);
        } elseif (isset($configButton['route'])) {
            $route = $configButton['route'];
            unset($configButton['route']);
        } elseif (isset($configButton['callback'])) {
            $route = $this->controller::createRoute('b-c', ['a' => $attributeName, 'i' => $buttonKey]);
            unset($configButton['callback']);
        } else {
            $route = MenuController::createRoute();
        }
        if (isset($configButton['hideCondition'])) {
            unset($configButton['hideCondition']);
        }

        return $route;
    }

    /**
     * @param $modelName
     * @param $attribute
     * @param $id
     *
     * @return string
     */
    public function createAttributeRoute($modelName, $attribute, $id)
    {
        if ($id) {
            $routeParams = [
                'm' => $modelName,
                'a' => $attribute,
                'i' => $id,
            ];
        } else {
            $routeParams = [
                'a' => $attribute,
            ];
        }

        return $this->controller::createRoute($id ? 'e-a' : 'sh-a', $routeParams);
    }
}
