<?php

namespace app\modules\bot\components;

use app\models\VacancyLanguage;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\request\Request;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use yii\base\InvalidConfigException;
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

    public function actionEnterAttribute($a, $t = null)
    {
        if (!$this->isRequestValid($a)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $state = $this->getState();
        $modelClass = $state->getIntermediateField('modelClass', null);
        $modelName = $this->getModelName($modelClass);
        $rule = $this->getRule($modelName);

        if ($this->isPrivateAttribute($a, $rule)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $attributes = $this->getAttributes($rule);
        $config = $attributes[$a];

        if (!is_string($t) || $t === '') {
            return $this->generatePublicResponse($modelName, $a, $config);
        }

        /* @var ActiveRecord $model */
        $model = $this->createModel($rule);
        $model->setAttribute($a, $t);
        if (!$model->validate($a)) {
            $errors = $model->getErrors($a);
            return $this->generatePublicResponse($modelName, $a, $attributes[$a], reset($errors));
        }
        $this->getState()->setIntermediateField($a, $t);

        $isEdit = !is_null($this->getState()->getIntermediateField('id', null));
        $nextAttribute = $this->getNextKey($attributes, $a);
Yii::error($nextAttribute);
        if (isset($nextAttribute) && !$isEdit) {
            return $this->generateResponse($modelName, $nextAttribute, $rule);
        }

        return $this->save($rule);
    }

    /**
     * @param string $a Attribute name
     * @param int $p Page number
     * @param null $v Attribute value
     * @return array
     */
    public function actionSetAttribute($a, $p = 1, $v = null)
    {
        if (!$this->isRequestValid($a)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $state = $this->getState();
        $modelClass = $state->getIntermediateField('modelClass', null);
        $modelName = $this->getModelName($modelClass);
        $rule = $this->getRule($modelName);

        if (!$this->isPrivateAttribute($a, $rule)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $attributes = $this->getAttributes($rule);
        $config = $attributes[$a];
        $relation = $this->getRelation($config);
        $relationAttributes = $relation['attributes'];

        if (isset($v) && isset($relation)) {
            $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
            if (!array_key_exists($relationAttributeName, $relationAttributes)) {
                return ResponseBuilder::fromUpdate($this->getUpdate())
                    ->answerCallbackQuery()
                    ->build();
            }
            $relationAttribute = $relationAttributes[$relationAttributeName];
            $relationModelName = $relationAttribute[0];
            $relationModel = call_user_func([ $relationModelName, 'findOne' ], $v);
            if (isset($relationModel)) {
                $relationData = $state->getIntermediateField($a, [[]]);
                $item = array_pop($relationData);
                if (empty($item)) {
                    foreach ($relationData as $key => $relationItem) {
                        if ($relationItem[$relationAttributeName] == $v)
                        {
                            $item = $relationItem;
                            unset($relationData[$key]);
                            break;
                        }
                    }
                }
                $relationAttributesCount = count($relationAttributes);
                $isManyToOne = $relationAttributesCount == 1;
                $item[$relationAttributeName] = $v;
                $relationData[] = $item;
                $state->setIntermediateField($a, $relationData);

                $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                $state->setIntermediateField('relationAttributeName', $nextRelationAttributeName);

                if (!isset($nextRelationAttributeName) && $isManyToOne) {
                    $isEdit = !is_null($state->getIntermediateField('id', null));
                    $nextAttribute = $this->getNextKey($attributes, $a);
                    if (isset($nextAttribute) && !$isEdit) {
                        return $this->generateResponse($modelName, $nextAttribute, $rule);
                    }

                    return $this->save($rule);
                }
            }
        }

        return $this->generatePrivateResponse($modelName, $a, $config, $p);
    }

    public function actionAddAttribute($a, $p = null)
    {
        if (!$this->isRequestValid($a)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $state = $this->getState();
        $modelClass = $state->getIntermediateField('modelClass', null);
        $rule = $this->getRule($this->getModelName($modelClass));
        $config = $rule['attributes'][$a];
        if (!isset($p)) {
            $relation = $this->getRelation($config);
            $attributeNames = array_keys($relation['attributes']);
            $relationAttributeName = reset($attributeNames);
            $relationAttribute = $relation['attributes'][$relationAttributeName];
            if ($relationAttribute[0] == $modelClass) {
                $relationAttributeName = next($attributeNames);
            }
            $state->setIntermediateField('relationAttributeName', $relationAttributeName);
            $attributeValue = $state->getIntermediateField($a, [[]]);
            $attributeLastItem = end($attributeValue);
            if (!empty($attributeLastItem)) {
                $attributeValue[] = [];
            }
            $state->setIntermediateField($a, $attributeValue);
        } else {
            $state->setIntermediateField('relationAttributeName', null);
        }
        return $this->generatePrivateResponse($this->getModelName($modelClass), $a, $config, $p ?? 1);
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
     * @return array
     */
    public function actionClearAttribute()
    {
        $state = $this->getState();
        $attributeName = $state->getIntermediateField('attributeName', null);
        if (isset($attributeName)) {
            $modelClass = $state->getIntermediateField('modelClass', null);
            $modelName = $this->getModelName($modelClass);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            $config = $attributes[$attributeName];

            $isAttributeRequired = $config['isRequired'] ?? true;
            if (!$isAttributeRequired) {
                $state->setIntermediateField($attributeName, null);

                $isEdit = !is_null($state->getIntermediateField('id', null));
                if ($isEdit) {
                    return $this->save($rule);
                }

                return $this->generateResponse($modelName, $attributeName, $config);
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionNextAttribute()
    {
        $state = $this->getState();
        $attributeName = $state->getIntermediateField('attributeName', null);
        if (isset($attributeName)) {
            $isEdit = !is_null($state->getIntermediateField('id', null));
            $modelClass = $state->getIntermediateField('modelClass', null);
            $modelName = $this->getModelName($modelClass);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
                if (isset($relationAttributeName)) {
                    $relationData = $state->getIntermediateField($attributeName, [[]]);
                    $item = array_pop($relationData);
                    if (!empty($item[$relationAttributeName] ?? null)) {
                        $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                        $state->setIntermediateField('relationAttributeName', $nextRelationAttributeName);
                        return $this->generatePrivateResponse($modelName, $attributeName, $attributes[$attributeName]);
                    }

                    return ResponseBuilder::fromUpdate($this->getUpdate())
                        ->answerCallbackQuery()
                        ->build();
                }
            }
            $nextAttributeName = $this->getNextKey($attributes, $attributeName);
            $isAttributeRequired = $attributes[$attributeName]['isRequired'] ?? true;
            if (!$isAttributeRequired || !empty($state->getIntermediateField($attributeName, null))) {
                if (isset($nextAttributeName) && !$isEdit) {
                        return $this->generateResponse($modelName, $nextAttributeName, $rule);
                } else {
                    return $this->save($rule);
                }
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    public function actionPrevAttribute()
    {
        $state = $this->getState();
        $attributeName = $state->getIntermediateField('attributeName', null);
        if (isset($attributeName)) {
            $isEdit = !is_null($state->getIntermediateField('id', null));
            $modelClass = $state->getIntermediateField('modelClass', null);
            $modelName = $this->getModelName($modelClass);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
                if (isset($relationAttributeName)) {
                    $nextRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                    $state->setIntermediateField('relationAttributeName', $nextRelationAttributeName);
                    return $this->generatePrivateResponse($modelName, $attributeName, $attributes[$attributeName]);
                }
            }
            $prevAttributeName = $this->getPrevKey($attributes, $attributeName);
            if (isset($prevAttributeName) && !$isEdit) {
                return $this->generateResponse($modelName, $prevAttributeName, $rule);
            } else {
                $response = $this->onCancel($rule['model'], $this->getState()->getIntermediateField('id', null));
                $this->getState()->reset();
                return $response;
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param $i int Item Primary Id
     * @return array
     */
    public function actionRemoveAttribute($i)
    {
        $state = $this->getState();
        $attributeName = $state->getIntermediateField('attributeName', null);
        if (isset($attributeName)) {
            $modelClass = $state->getIntermediateField('modelClass', null);
            $modelName = $this->getModelName($modelClass);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $secondaryRelationAttributeName = reset(array_keys($relationAttributes));
                $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
                if (!isset($relationAttributeName)) {
                    $items = $state->getIntermediateField($attributeName, []);
                    foreach ($items as $key => $item) {
                        if ($item[$secondaryRelationAttributeName] == $i)
                        {
                            unset($items[$key]);
                            break;
                        }
                    }
                    $state->setIntermediateField($attributeName, $items);
                    return $this->generatePrivateResponse($modelName, $attributeName, $attributes[$attributeName]);
                }
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param $i int Item Primary Id
     * @return array
     */
    public function actionEditRelationAttribute($i)
    {
        $state = $this->getState();
        $attributeName = $state->getIntermediateField('attributeName', null);
        if (isset($attributeName)) {
            $modelClass = $state->getIntermediateField('modelClass', null);
            $modelName = $this->getModelName($modelClass);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $secondaryRelationAttributeName = reset(array_keys($relationAttributes));
                array_shift($relationAttributes);
                $noneValueRelationAttributeName = reset(array_keys($relationAttributes));
                $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
                if (!isset($relationAttributeName)) {
                    $state->setIntermediateField('relationAttributeName', $noneValueRelationAttributeName);
                    $items = $state->getIntermediateField($attributeName, []);
                    foreach ($items as $key => $item) {
                        if ($item[$secondaryRelationAttributeName] == $i)
                        {
                            unset($items[$key]);
                            $items[] = $item;
                            break;
                        }
                    }
                    $state->setIntermediateField($attributeName, $items);
                    return $this->generatePrivateResponse($modelName, $attributeName, $attributes[$attributeName]);
                }
            }
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
    protected function beforeCreate(string $className)
    {
        $this->getState()->setIntermediateField('modelClass', $className);
    }

    /**
     * @param array $rule
     * @param string $attributeName
     * @param int $id
     */
    protected function beforeEdit(array $rule, string $attributeName, int $id)
    {
        $state = $this->getState();
        $model = $this->getModel($rule, $id);
        if (isset($model)) {
            $state->setIntermediateField('modelClass', $rule['model']);
            $state->setIntermediateField('id', $id);

            if ($this->isPrivateAttribute($attributeName, $rule))
            {
                $relation = $this->getRelation($this->getAttributes($rule)[$attributeName]);
            }
            if (isset($relation) && count($relation['attributes']) > 1)
            {
                $relationModelClass = $relation['model'];
                $relationAttributeName = reset(array_keys($relation['attributes']));
                $relationAttributeRefColumn = $relation['attributes'][$relationAttributeName][1];
                $relationModels = call_user_func([ $relationModelClass, 'findAll' ], [ $relationAttributeName => $model->getAttribute($relationAttributeRefColumn) ]);
                $value = [];
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                /* @var ActiveRecord $relationModel */
                foreach ($relationModels as $relationModel) {
                    $relationItem = [];
                    foreach ($relationAttributes as $relationAttributeName => $relationAttribute)
                    {
                        $relationItem[$relationAttributeName] = $relationModel->getAttribute($relationAttributeName);
                    }
                    $value[] = $relationItem;
                }
            }
            else
            {
                $value = $model->getAttribute($attributeName);
            }


            if (isset($value)) {
                $state->setIntermediateField($attributeName, $value);
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
            $manyToManyRelationAttributes = [];
            foreach ($rule['attributes'] as $attributeName => $attribute) {
                if ($state->isIntermediateFieldExists($attributeName)) {
                    $relation = $this->getRelation($attribute);
                    if (isset($relation)) {
                        $relationAttributes = $relation['attributes'];
                        if (count($relationAttributes) > 1) {
                            $manyToManyRelationAttributes[] = $attributeName;
                            continue;
                        }
                        if (count($relation) == 1) {
                            $relationValue = $state->getIntermediateField($attributeName, [[]]);
                            $model->setAttributes($relationValue[0]);
                        }
                    } else {
                        $value = $state->getIntermediateField($attributeName, null);
                        $model->setAttribute($attributeName, $value ?? null);
                    }
                }
            }
            $this->beforeSave($model, $isNew);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    foreach ($manyToManyRelationAttributes as $attributeName) {
                        $relation = $this->getRelation($rule['attributes'][$attributeName]);
                        $relationModelClass = $relation['model'];
                        $relationAttributes = $relation['attributes'];

                        $primaryRelationAttributeName = array_keys($relationAttributes)[0];
                        $primaryRelationAttribute = $relationAttributes[$primaryRelationAttributeName];
                        $primaryRelationAttributeRefColumn = $primaryRelationAttribute[1];
                        $secondaryRelationAttributeName = array_keys($relationAttributes)[1];

                        $attributeValues = $state->getIntermediateField($attributeName, []);
                        $secondaryAttributeIds = [];
                        foreach ($attributeValues as $attributeValue) {
                            /** @var ActiveRecord $relationModel */
                            $relationModel = call_user_func([ $relationModelClass, 'findOne' ], [
                                    $primaryRelationAttributeName => $model->getAttribute($primaryRelationAttributeRefColumn),
                                    $secondaryRelationAttributeName => $attributeValue[$secondaryRelationAttributeName],
                                ])
                                ?? Yii::createObject([
                                'class' => $relationModelClass,
                            ]);
                            $secondaryAttributeIds[] = $attributeValue[$secondaryRelationAttributeName];
                            $relationModel->setAttribute($primaryRelationAttributeName, $model->getAttribute($primaryRelationAttributeRefColumn));
                            $relationModel->setAttributes($attributeValue);
                            if (!$relationModel->save()) {
                                return ResponseBuilder::fromUpdate($this->getUpdate())
                                    ->answerCallbackQuery()
                                    ->build();
                            }
                        }

                        if (!$isNew) {
                            /* @var ActiveQuery $query */
                            $query = call_user_func([$relationModelClass, 'find'], []);
                            $itemsToDelete = $query->where(['not', [$secondaryRelationAttributeName => $secondaryAttributeIds]])->andWhere([$primaryRelationAttributeName => $model->id])->all();
                            foreach ($itemsToDelete as $itemToDelete) {
                                $itemToDelete->delete();
                            }
                        }
                    }

                    $state->reset();
                    $transaction->commit();
                    return $this->afterSave($model, $isNew);
                }
            } catch (\Exception $ex) {
                Yii::warning($ex);
                $transaction->rollBack();
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param array $assocArray
     * @param $element
     * @return mixed|null
     */
    private function getNextKey(array $assocArray, $element)
    {
        $keys = array_keys($assocArray);
        return $keys[array_search($element, $keys) + 1] ?? null;
    }

    /**
     * @param array $assocArray
     * @param $element
     * @return mixed|null
     */
    private function getPrevKey(array $assocArray, $element)
    {
        $keys = array_keys($assocArray);
        return $keys[array_search($element, $keys) - 1] ?? null;
    }

    private function generatePrivateResponse(string $modelName, string $attributeName, array $config, int $page = 1)
    {
        $state = $this->getState();
        $state->setName(self::createRoute('set-attribute', [
            'a' => $attributeName,
            'p' => $page,
        ]));
        $state->setIntermediateField('attributeName', $attributeName);

        $relationAttributeName = $state->getIntermediateField('relationAttributeName', null);
        $isEdit = !is_null($state->getIntermediateField('id', null));
        list($step, $totalSteps) = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $relation = $this->getRelation($config);
        if (isset($relationAttributeName)) {
            $relationAttribute = $relation['attributes'][$relationAttributeName];
            /* @var ActiveQuery $query */
            $query = call_user_func([ $relationAttribute[0] , 'find'], []);
            $valueAttribute = $relationAttribute[1];
            $itemButtons = PaginationButtons::buildFromQuery(
                $query,
                function (int $page) use ($modelName, $attributeName) {
                    return self::createRoute('set-attribute', [
                        'a' => $attributeName,
                        'p' => $page,
                    ]);
                },
                function (ActiveRecord $model) use ($modelName, $attributeName, $valueAttribute) {
                    return [
                        'text' => $this->getLabel($model),
                        'callback_data' => self::createRoute('set-attribute', [
                            'a' => $attributeName,
                            'v' => $model->getAttribute($valueAttribute),
                        ]),
                    ];
                },
                $page
            );

            $attributeValue = $state->getIntermediateField($attributeName, []);
            if (!empty($attributeValue)) {
                $item = $attributeValue[0];
                $relationAttributeValue = $item[$relationAttributeName];
                $currentValue = $query->where([ $valueAttribute => $relationAttributeValue ])->one();
            }
            $systemButtons = $this->generateSystemButtons($modelName, $attributeName, empty($attributeValue));

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render("$modelName/$attributeName/set-$relationAttributeName", [
                        'currentValue' => $currentValue ?? null,
                        'step' => $step,
                        'totalSteps' => $totalSteps,
                        'isEdit' => $isEdit,
                    ]),
                    array_merge($itemButtons, [ $systemButtons ]),
                    true
                )
                ->build();
        }

        $isAttributeRequired = $config['isRequired'] ?? true;
        $relationAttributesWithoutPrimaryAttribute = $relation['attributes'];
        array_shift($relationAttributesWithoutPrimaryAttribute);
        $secondaryRelationAttributeName = reset(array_keys($relationAttributesWithoutPrimaryAttribute));
        $items = $state->getIntermediateField($attributeName, []);
        $itemButtons = PaginationButtons::buildFromArray(
            $items,
            function (int $page) use ($attributeName) {
                return self::createRoute('add-attribute', [
                    'a' => $attributeName,
                    'p' => $page,
                ]);
            },
            function (array $item) use ($relation, $secondaryRelationAttributeName, $isAttributeRequired, $items) {
                /* @var ActiveRecord $model */
                $model = Yii::createObject(array_merge([ 'class' => $relation['model'] ], $item));
                return array_merge([
                        [
                            'text' => $this->getLabel($model),
                            'callback_data' => self::createRoute('edit-relation-attribute', [
                                'i' => $item[$secondaryRelationAttributeName],
                            ]),
                        ]
                    ],
                    count($items) == 1 && $isAttributeRequired ? [] : [
                        [
                        'text' => Emoji::DELETE,
                        'callback_data' => self::createRoute('remove-attribute', [
                            'i' => $item[$secondaryRelationAttributeName],
                        ]),
                    ]
                ]);
            },
            $page
        );
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render("$modelName/set-$attributeName", [
                    'step' => $step,
                    'totalSteps' => $totalSteps,
                    'isEdit' => $isEdit,
                ]),
                array_merge($itemButtons, [ $this->generateSystemButtons($modelName, $attributeName, empty($items)) ])
            )
            ->build();
    }

    private function generatePublicResponse(string $modelName, string $attributeName, array $config, string $error = null)
    {
        $state = $this->getState();
        $state->setName(self::createRoute('enter-attribute', [
            'a' => $attributeName,
        ]));
        $state->setIntermediateField('attributeName', $attributeName);

        $isEdit = !is_null($state->getIntermediateField('id', null));
        $attributeValue = $state->getIntermediateField($attributeName, null);
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
            $attributes = $config['relation']['attributes'];
            $this->getState()->setIntermediateField('relationAttributeName', (count($attributes) == 1) ? array_keys($attributes)[0] : null);
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
        $relation = $this->getRelation($config);
        $relationAttributeName = $this->getState()->getIntermediateField('relationAttributeName', null);

        /* 'Back' button */
        $systemButtons[] = [
            'text' => Emoji::BACK,
            'callback_data' => self::createRoute('prev-attribute'),
        ];

        if (!isset($relation) || count($relation['attributes']) == 1) {
            /* 'Clear' button */
            if (!$isAttributeRequired && !$isEmpty) {
                $systemButtons[] = [
                    'text' => Emoji::DELETE,
                    'callback_data' => self::createRoute('clear-attribute')
                ];
            }
        } else {
            /* 'Add' button */
            $systemButtons[] = [
                'text' => Emoji::ADD,
                'callback_data' => self::createRoute('add-attribute', [
                    'a' => $attributeName,
                ]),
            ];
        }

        /* 'Next' button */
        if (((!$isAttributeRequired || !$isEmpty) && (!$isEdit || (isset($relation) && count($relation['attributes']) > 1)
                && !isset($relationAttributeName)))) {
            $nextAttribute = $this->getNextKey($attributes, $attributeName);
            $systemButtons[] = [
                'text' => Yii::t('bot', (isset($nextAttribute) && !$isEdit) ? 'Next' : 'Finish'),
                'callback_data' => self::createRoute('next-attribute'),
            ];
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

    /**
     * If attribute has relation, this method perform its validation and return an array on success, otherwise null.
     *
     * @param array $attributeConfig
     * @return array|null
     */
    private function getRelation(array $attributeConfig)
    {
        if (array_key_exists('relation', $attributeConfig)) {
            $relation = $attributeConfig['relation'];
            if (!is_array($relation)) {
                Yii::warning('\'relation\' must have an array as its value.');
                return null;
            }
        }
        if (isset($relation) && array_key_exists('attributes', $relation) ) {
            $attributes = $relation['attributes'];
        }
        if (!empty($attributes)) {
            $attributesCount = count($attributes);
            if ($attributesCount <= 1 && array_key_exists('model', $relation)) {
                Yii::warning('When using many-to-many relationship, \'model\' can`t be empty and count of attributes must be greater than 1.');
                return null;
            }
            if ($attributesCount != 1 && !array_key_exists('model', $relation)) {
                Yii::warning('When using many-to-one relationship, \'model\' must be empty and count of attributes must be equal to one.');
                return null;
            }
            foreach ($attributes as $attribute => $config) {
                if (count($config) != 2) {
                    Yii::warning("Error occurred when reading '$config' attribute: its value must be an array with model name in 0th index and ref columng in 1th index");
                    return null;
                }
                $modelClassName = $config[0];
                $refColumn = $config[1];
                try {
                    /* @var $model ActiveRecord */
                    $model = Yii::createObject($modelClassName);
                } catch (InvalidConfigException $ex) {
                    Yii::warning("$modelClassName doesn't exist.");
                    return null;
                }
                if (!($model instanceof ActiveRecord)) {
                    Yii::warning("$modelClassName must be inherited from " . ActiveRecord::class);
                    return null;
                }
                if (!$model->hasAttribute($refColumn)) {
                    Yii::warning("$modelClassName doesn't have $refColumn attribute");
                    return null;
                }
            }

            return $relation;
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

    /**
     * @param string $attributeName
     * @return bool
     */
    private function isRequestValid(string $attributeName)
    {
        $state = $this->getState();
        $modelClass = $state->getIntermediateField('modelClass', null);
        $stateRoute = $state->getName();
        if (isset($stateRoute)) {
            $stateRequest = Request::fromUrl($stateRoute);
        }
        if (isset($stateRequest)) {
            $stateAttributeName = $stateRequest->getParam('a', null);
        }
        if (isset($modelClass)) {
            $rule = $this->getRule($this->getModelName($modelClass));
        }
        if (isset($rule)) {
            $attributes = $this->getAttributes($rule);
        }
        if (!empty($attributes) && isset($stateAttributeName) && array_key_exists($attributeName, $attributes)) {
            if ($stateAttributeName == $attributeName) {
                return true;
            }
            if ($this->canSkipAttribute($attributes, $stateAttributeName)
                && $this->getNextKey($attributes, $stateAttributeName) == $attributeName) {
                return true;
            }
            if ($this->getPrevKey($attributes, $stateAttributeName) == $attributeName) {
                return true;
            }
        }
        return false;
    }
}
