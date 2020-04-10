<?php

namespace app\modules\bot\components;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\request\Request;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

abstract class CrudController extends Controller
{
    public function actionCreate($m)
    {
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes)) {
            $this->beforeCreate($rule['model']);
            $attribute = array_keys($attributes)[0];
            return $this->generateResponse($m, $attribute, $rule);
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionEnterAttribute($m, $a, $t = null)
    {
        $stateRequest = Request::fromUrl($this->getState()->getName());
        if ($m == $stateRequest->getParam('m', null)) {
            $rule = $this->getRule($m);
            $attributes = $this->getAttributes($rule);
            $stateAttribute = $stateRequest->getParam('a', null);
            if (!empty($attributes) && array_key_exists($a, $attributes)
                && ($stateAttribute == $a
                    || ($this->canSkipAttribute($attributes, $stateAttribute) && $this->getNextElement(array_keys($attributes), $stateAttribute) == $a)
                    || $this->getPrevElement(array_keys($attributes), $stateAttribute) == $a)) {
                if (empty($t)) {
                    return $this->generatePublicResponse($m, $a, $attributes[$a]);
                }

                /* @var ActiveRecord $model */
                $model = $this->createModel($rule);
                $model->setAttribute($a, $t);
                if (!$model->validate($a)) {
                    $errors = $model->getErrors($a);
                    return $this->generatePublicResponse($m, $a, $attributes[$a], reset($errors));
                }
                $this->getState()->setIntermediateField($a, $t);

                $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
                $nextAttribute = $this->getNextElement(array_keys($attributes), $a);
                if (isset($nextAttribute) && !$isEdit) {
                    return $this->generateResponse($m, $nextAttribute, $rule);
                }

                return $this->save($rule);
            }
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionSetAttribute($m, $a, $p = 1, $v = null)
    {
        $stateRequest = Request::fromUrl($this->getState()->getName());
        if ($m == $stateRequest->getParam('m', null)) {
            $rule = $this->getRule($m);
            $attributes = $this->getAttributes($rule);
            $stateAttribute = $stateRequest->getParam('a', null);
            if (!empty($attributes) && array_key_exists($a, $attributes)
                && ($stateAttribute == $a
                    || ($this->canSkipAttribute($attributes, $stateAttribute) && $this->getNextElement(array_keys($attributes), $stateAttribute) == $a)
                    || $this->getPrevElement(array_keys($attributes), $stateAttribute) == $a)) {
                $config = $attributes[$a];

                if (isset($v)) {
                    $relationModel = call_user_func([ $config['relation']['model'], 'findOne' ], $v);
                    if (isset($relationModel)) {
                        $this->getState()->setIntermediateField($a, $v);

                        $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
                        $nextAttribute = $this->getNextElement(array_keys($attributes), $a);
                        if (isset($nextAttribute) && !$isEdit) {
                            return $this->generateResponse($m, $nextAttribute, $rule);
                        }

                        return $this->save($rule);
                    }
                }

                return $this->generatePrivateResponse($m, $a, $config, $p);
            }
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionEditAttribute($m, $a, $id)
    {
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes) && array_key_exists($a, $attributes) && isset($id)) {
            $this->beforeEdit($rule, $a, $id);
            return $this->generateResponse($m, $a, $rule);
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param string $m Model name
     * @param string $a Attribute name
     * @return array
     */
    public function actionClearAttribute($m, $a)
    {
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (isset($rule) && array_key_exists($a, $attributes)) {
            $isAttributeRequired = $attributes[$a]['isRequired'] ?? true;
            if (!$isAttributeRequired) {
                $state = $this->getState();
                $state->setIntermediateField($a, null);
                $isEdit = !is_null($state->getIntermediateField('id', null));
                if ($isEdit) {
                    return $this->save($rule);
                }
            }
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionFinish(string $modelName)
    {
        $rule = $this->getRule($modelName);
        if (!empty($rule) && ($attributes = $this->getAttributes($rule))) {
            $stateRequest = Request::fromUrl($this->getState()->getName());
            $stateAttribute = $stateRequest->getParam('a', null);
            $attributeValue = $this->getState()->getIntermediateField($stateAttribute, null);
            $lastAttribute = end(array_keys($attributes));
            if ($stateAttribute == $lastAttribute && !empty($attributeValue)) {
                $this->save($rule);
            }
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param string $m
     * @return array
     */
    public function actionCancel(string $m)
    {
        $rule = $this->getRule($m);
        if (isset($rule)) {
            $response = $this->onCancel($rule['model'], $this->getState()->getIntermediateField('id', null));
            $this->getState()->reset();
            return $response;
        }
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param string $m Model name
     * @param int $id Model id
     * @return array
     */
    public function actionUpdate($m, $id)
    {
        $modelName = $m;
        $rule = $this->getRule($modelName);
        $attributes = array_keys($rule['attributes']);

        /* @var ActiveRecord $model */
        $model = $this->getModel($rule, $id);
        $editButtons = array_map(function (string $attribute) use ($id, $modelName, $model, $rule) {
            return [
                [
                    'text' => Yii::t('bot', $model->getAttributeLabel($attribute)),
                    'callback_data' => self::createRoute('edit-attribute', [
                        'id' => $id,
                        'm' => $modelName,
                        'a' => $attribute,
                    ]),
                ]
            ];
        }, $attributes);


        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render("$modelName/show", [
                    $modelName => $model,
                ]),
                array_merge($editButtons, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute('show', [
                                'id' => $id,
                                'm' => $modelName,
                            ]),
                        ],
                    ],
                ])
            )
            ->build();
    }

    /**
     * @param int $m Model name
     * @param int $id Model id
     * @return array
     */
    public function actionShow($m, $id)
    {
        $rule = $this->getRule($m);
        $model = $this->getModel($rule, $id);
        if (!isset($model)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $keyboard = $this->getKeyboard($m, $model);
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render("$m/show", [
                    $m => $model,
                ]),
                $keyboard ?? null,
                true
            )
            ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     * @return array
     */
    abstract protected function afterSave(ActiveRecord $model, bool $isNew);

    /**
     * @param string $className
     */
    protected function beforeCreate(string $className) { }

    /**
     * @param array $rule
     * @param string $attribute
     * @param int $id
     */
    protected function beforeEdit(array $rule, string $attribute, int $id)
    {
        $state = $this->getState();
        $model = $this->getModel($rule, $id);
        if (isset($model)) {
            $state->setIntermediateField('id', $id);
            $value = $model->getAttribute($attribute);
            if (isset($value)) {
                $state->setIntermediateField($attribute, $value);
            }
        }
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
     */
    protected function beforeSave(ActiveRecord $model, bool $isNew) { }

    /**
     * @param string $className
     * @return string
     */
    protected function getModelName(string $className)
    {
        $parts = explode('\\', $className);
        return strtolower(array_pop($parts));
    }

    protected function rules()
    {
        return [];
    }

    /**
     * @param string $className
     * @param int|null $id
     * @return array
     */
    protected function onCancel(string $className, ?int $id)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    private function save(array $rule)
    {
        $state = $this->getState();
        $id = $state->getIntermediateField('id', null);
        $isNew = is_null($id);
        /* @var ActiveRecord $model */
        if ($isNew) {
            $model = $this->createModel($rule);
        } else {
            $model = $this->getModel($rule, $id);
        }

        if ($model) {
            foreach (array_keys($rule['attributes']) as $attribute) {
                if ($state->isIntermediateFieldExists($attribute)) {
                    $value = $state->getIntermediateField($attribute, null);
                    $model->setAttribute($attribute, $value);
                }
            }
            $this->beforeSave($model, $isNew);
            if ($model->save()) {
                $state->reset();
                return $this->afterSave($model, $isNew);
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param array $array
     * @param $element
     * @return mixed|null
     */
    private function getNextElement(array $array, $element)
    {
        return $array[array_search($element, $array) + 1] ?? null;
    }

    /**
     * @param array $array
     * @param $element
     * @return mixed|null
     */
    private function getPrevElement(array $array, $element)
    {
        return $array[array_search($element, $array) - 1] ?? null;
    }

    private function generatePrivateResponse(string $modelName, string $attributeName, array $config, int $page = 1)
    {
        $this->getState()->setName(self::createRoute('set-attribute', [
            'm' => $modelName,
            'a' => $attributeName,
            'p' => $page,
        ]));
        /* @var ActiveQuery $query */
        $query = call_user_func([ $config['relation']['model'], 'find' ], []);
        $itemButtons = PaginationButtons::buildFromQuery(
            $query,
            function (int $page) use ($modelName, $attributeName) {
                return self::createRoute('set-attribute', [
                    'm' => $modelName,
                    'a' => $attributeName,
                    'p' => $page,
                ]);
            },
            function (ActiveRecord $model) use ($modelName, $attributeName) {
                return [
                    'text' => $this->getLabel($model),
                    'callback_data' => self::createRoute('set-attribute', [
                        'm' => $modelName,
                        'a' => $attributeName,
                        'v' => $model->id,
                    ]),
                ];
            },
            $page
        );

        $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
        $attributeValue = $this->getState()->getIntermediateField($attributeName, null);
        $systemButtons = $this->generateSystemButtons($modelName, $attributeName, empty($attributeValue));
        list($step, $totalSteps) = $this->getStepsInfo($attributeName, $this->getRule($modelName));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render("$modelName/set-$attributeName", [
                    'currentValue' => /*$query->where([ $relation['foreign_key'] => $attributeValue ])*/ null,
                    'step' => $step,
                    'totalSteps' => $totalSteps,
                    'isEdit' => $isEdit,
                ]),
                array_merge($itemButtons, [ $systemButtons ]),
                true
            )
            ->build();
    }

    private function generatePublicResponse(string $modelName, string $attributeName, array $config, string $error = null)
    {
        $this->getState()->setName(self::createRoute('enter-attribute', [
            'm' => $modelName,
            'a' => $attributeName,
        ]));

        $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
        $attributeValue = $this->getState()->getIntermediateField($attributeName, null);
        $systemButtons = $this->generateSystemButtons($modelName, $attributeName, empty($attributeValue));
        list($step, $totalSteps) = $this->getStepsInfo($attributeName, $this->getRule($modelName));

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render("$modelName/set-$attributeName", [
                    'currentValue' => $attributeValue,
                    'error' => $error,
                    'step' => $step,
                    'totalSteps' => $totalSteps,
                    'isEdit' => $isEdit,
                ]),
                [ $systemButtons ],
                true
            )
            ->build();
    }

    private function generateResponse(string $modelName, string $attributeName, array $rule)
    {
        $config = $rule['attributes'][$attributeName];
        if ($this->isPrivateAttribute($attributeName, $rule)) {
            return $this->generatePrivateResponse($modelName, $attributeName, $config);
        } else {
            return $this->generatePublicResponse($modelName, $attributeName, $config);
        }
    }

    private function generateSystemButtons(string $modelName, string $attributeName, bool $isEmpty)
    {
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $systemButtons = [];

        $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
        $isAttributeRequired = $config['isRequired'] ?? true;

        /* 'Back' button */
        if (!$isEdit && $prevAttribute = $this->getPrevElement(array_keys($attributes), $attributeName)) {
            $action = $this->isPrivateAttribute($prevAttribute, $rule) ? 'set-attribute' : 'enter-attribute';
            $systemButtons[] = [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute($action, [
                    'm' => $modelName,
                    'a' => $prevAttribute,
                ]),
            ];
        } else {
            $systemButtons[] = [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute('cancel', [
                    'm' => $modelName,
                ]),
            ];
        }

        /* 'Clear' button */
        if (!$isAttributeRequired && !$isEmpty) {
            $systemButtons[] = [
                'text' => Emoji::DELETE,
                'callback_data' => self::createRoute('clear-attribute', [
                    'm' => $modelName,
                    'a' => $attributeName,
                ])
            ];
        }

        /* 'Skip' button */
        if ((!$isAttributeRequired || !$isEmpty) && !$isEdit) {
            if ($nextAttribute = $this->getNextElement(array_keys($attributes), $attributeName)) {
                $action = $this->isPrivateAttribute($nextAttribute, $rule) ? 'set-attribute' : 'enter-attribute';
                $systemButtons[] = [
                    'text' => Yii::t('bot', 'Skip'),
                    'callback_data' => self::createRoute($action, [
                        'm' => $modelName,
                        'a' => $nextAttribute,
                    ])
                ];
            } else {
                $systemButtons[] = [
                    'text' => Yii::t('bot', 'Finish'),
                    'callback_data' => self::createRoute('finish', [
                        'm' => $modelName,
                    ])
                ];
            }
        }

        return $systemButtons;
    }

    private function getStepsInfo(string $attributeName, array $rule)
    {
        $attributes = $this->getAttributes($rule);
        $totalSteps = count($attributes);
        $step = array_search($attributeName, array_keys($attributes)) + 1;
        return [ $step, $totalSteps ];
    }

    private function isPrivateAttribute(string $attributeName, array $rule)
    {
        $config = $rule['attributes'][$attributeName];
        return array_key_exists('relation', $config);
    }

    /**
     * @param array $rule
     * @return object|null
     */
    private function createModel(array $rule)
    {
        if (!array_key_exists('model', $rule)) {
            Yii::warning('Rule must contain the \'model\' key');
            return null;
        }
        try {
            $object = Yii::createObject([ 'class' => $rule['model'] ]);
            if ($object instanceof ActiveRecord) {
                return $object;
            }
            Yii::warning('The \'model\' key must contain a name of the class that is inherited from ' . ActiveRecord::class);
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param array $rule
     * @param int $id
     * @return ActiveRecord|null
     */
    private function getModel(array $rule, int $id)
    {
        $modelName = $this->getModelName($rule['model']);
        $getModelMethodName = 'get' . ucfirst($modelName);
        if (method_exists($this, $getModelMethodName)) {
            /* @var ActiveRecord $model */
            $model = call_user_func([ $this, $getModelMethodName ], $id);
        } else {
            Yii::warning("You must declare a '$getModelMethodName' method to make 'show' action work.");
        }
        return $model ?? null;
    }

    /**
     * @param $modelName
     * @param $model
     * @return array
     */
    private function getKeyboard($modelName, $model)
    {
        $getKeyboardMethodName = 'get' . ucfirst($modelName) . 'Keyboard';
        if (method_exists($this, $getKeyboardMethodName)) {
            $keyboard = call_user_func([ $this, $getKeyboardMethodName ], $model);
        }
        return $keyboard ?? null;
    }

    /**
     * @param string $modelName
     * @return mixed|null
     */
    private function getRule(string $modelName)
    {
        $requestedRule = null;
        foreach ($this->rules() as $rule) {
            if ($this->getModelName($rule['model']) == $modelName) {
                $requestedRule = $rule;
            }
        }
        return $requestedRule;
    }

    /**
     * @param array $rule
     * @return mixed|null
     */
    private function getAttributes(array $rule)
    {
        if (is_array($rule) && array_key_exists('attributes', $rule) && is_array($rule['attributes'])) {
            return $rule['attributes'];
        }
        return null;
    }

    private function getLabel(ActiveRecord $model)
    {
        $methodName = 'get' . ucfirst($this->getModelName(get_class($model))) . 'Label';
        return $this->$methodName($model);
    }

    private function canSkipAttribute(array $attributes, string $attributeName)
    {
        $config = $attributes[$attributeName];
        $isRequired = $config['isRequired'] ?? true;
        $isEmptyAttribute = empty($this->getState()->getIntermediateField($attributeName, null));
        return !$isRequired || !$isEmptyAttribute;
    }
}
