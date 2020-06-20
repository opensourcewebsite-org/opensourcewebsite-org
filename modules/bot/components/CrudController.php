<?php

namespace app\modules\bot\components;

use app\components\helpers\ArrayHelper;
use app\models\VacancyLanguage;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\request\Request;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\rules\FieldInterface;
use app\modules\bot\controllers\privates\MenuController;
use app\modules\bot\services\BackRouteService;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class CrudController
 *
 * @package app\modules\bot\components
 */
abstract class CrudController extends Controller
{
    const FIELD_NAME_RELATION    = 'relationAttributeName';
    const FIELD_NAME_MODEL_CLASS = 'modelClass';
    const FIELD_NAME_ATTRIBUTE   = 'attributeName';

    /** @var BackRouteService */
    public $backRoute;

    /** @inheritDoc */
    public function __construct($id, $module, $config = [])
    {
        $this->backRoute = Yii::createObject(
            [
                'class'      => BackRouteService::class,
                'state'      => $module->userState,
                'controller' => $this,
            ]
        );
        parent::__construct($id, $module, $config);
    }

    /**
     * @param string $m $this->getModelName(Model::class)
     *
     * @return array
     */
    public function actionCreate($m)
    {
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes)) {
            $this->beforeCreate($rule['model']);
            $attribute = array_keys($attributes)[0];

            return $this->generateResponse($m, $attribute, compact('rule'));
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->answerCallbackQuery()
                              ->build();
    }

    /**
     * [
     * 'keywords'      => [
     *  'component' => [
     *      'class'      => ExplodeStringField::class,
     *      'attributes' => [
     *          'delimiters' => [',', '.', "\n"],
     *      ],
     * ],
     * 'location'      => [
     *      'component' => LocationToArrayField::class,
     * ],
     *
     * @param $attribute
     *
     * @return object|null
     * @throws InvalidConfigException
     */
    private function createAttributeComponent($attribute)
    {
        if (isset($attribute['component'])) {
            $component = $attribute['component'];
            $objectParams = [];
            if (is_array($component) && isset($component['class'])) {
                $objectParams['class'] = $component['class'];
                $objectParams = array_merge($objectParams, $component['attributes'] ?? []);
            } else {
                $objectParams['class'] = $component;
            }

            return Yii::createObject($objectParams, [$this]);
        }

        return null;
    }

    /**
     * @param string       $modelName
     * @param string|array $fieldName
     *
     * @return string|array
     */
    private function createIntermediateFieldName($modelName, $fieldName)
    {
        if (is_array($fieldName)) {
            $names = [];
            foreach ($fieldName as $key => $item) {
                $names[$this->createIntermediateFieldName($modelName, $key)] = $item;
            }

            return $names;
        }

        return $modelName . $fieldName;
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param string $value
     */
    private function setIntermediateField($modelName, $attributeName, $value)
    {
        $this->getState()->setIntermediateField($this->createIntermediateFieldName($modelName, $attributeName), $value);
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param null   $defaultValue
     *
     * @return mixed|null
     */
    private function getIntermediateField($modelName, $attributeName, $defaultValue = null)
    {
        return $this->getState()->getIntermediateField(
            $this->createIntermediateFieldName($modelName, $attributeName),
            $defaultValue
        );
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array  $values
     */
    private function setIntermediateFields($modelName, $values)
    {
        $this->getState()->setIntermediateFields($this->createIntermediateFieldName($modelName, $values));
    }

    /**
     * @return string
     */
    private function getCurrentModelClass()
    {
        return $this->getState()->getIntermediateField(self::FIELD_NAME_MODEL_CLASS, null);
    }

    /**
     * @param string $modelClass
     */
    private function setCurrentModelClass($modelClass)
    {
        $this->getState()->setIntermediateField(self::FIELD_NAME_MODEL_CLASS, $modelClass);
    }

    public function actionEnterAttribute($a, $text = null)
    {
        $attributeName = $a;
        if (!$this->isRequestValid($attributeName)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->answerCallbackQuery()
                                  ->build();
        }

        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $rule = $this->getRule($modelName);

        if ($this->isPrivateAttribute($attributeName, $rule)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->answerCallbackQuery()
                                  ->build();
        }

        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];

        if (!is_string($text) || $text === '') {
            return $this->generatePublicResponse($modelName, $attributeName, compact('config'));
        }

        /* @var ActiveRecord $model */
        $model = $this->createModel($rule);
        $currentAttribute = $attributes[$attributeName];
        $fieldResult = $text;
        $component = $this->createAttributeComponent($currentAttribute);
        if ($component instanceof FieldInterface) {
            $fieldResult = $component->prepare($text);
        }
        if (is_array($fieldResult)) {
            $model->setAttributes($fieldResult);
        } else {
            $model->setAttribute($attributeName, $fieldResult);
        }
        if (!$model->validate($attributeName)) {
            $errors = $model->getErrors($attributeName);

            return $this->generatePublicResponse(
                $modelName,
                $attributeName,
                ['config' => $currentAttribute, 'error' => reset($errors)]
            );
        }
        if (is_array($fieldResult)) {
            $this->setIntermediateFields($modelName, $fieldResult);
        } else {
            $this->setIntermediateField($modelName, $attributeName, $fieldResult);
        }

        $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
        $nextAttribute = $this->getNextKey($attributes, $attributeName);

        if (isset($nextAttribute) && !$isEdit) {
            return $this->generateResponse($modelName, $nextAttribute, compact('rule'));
        }

        return $this->save($rule);
    }

    /**
     * @param string $a    Attribute name
     * @param int    $p    Page number
     * @param null   $v    Attribute value
     * @param null   $text User Message
     *
     * @return array
     */
    public function actionSetAttribute($a, $p = 1, $v = null, $text = null)
    {
        $attributeName = $a;
        if (!$this->isRequestValid($attributeName)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->answerCallbackQuery()
                                  ->build();
        }

        $state = $this->getState();
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $rule = $this->getRule($modelName);

        if (!$this->isPrivateAttribute($attributeName, $rule)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->answerCallbackQuery()
                                  ->build();
        }

        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $relation = $this->getRelation($config);
        $relationAttributes = $relation['attributes'];
        [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes($modelClass, $relation);
        $isValidRequest = false;
        $component = $this->createAttributeComponent($config);
        if ($component instanceof FieldInterface) {
            $text = $component->prepare($text);
        }

        $error = null;
        if (isset($relation) && (isset($v) || isset($text))) {
            $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
            if (!$relationAttributeName && $secondaryRelation) {
                $isValidRequest = true;
                $relationData = $this->getIntermediateField($modelName, $attributeName, [[]]);
                if (is_array($text)) {
                    $relationData = array_merge($relationData, $text);
                } else {
                    $relationData[] = $text;
                }
                $relationData = array_filter(
                    $relationData,
                    function ($val) {
                        return $val;
                    }
                );
                $this->setIntermediateField($modelName, $attributeName, $relationData);
            } else {
                if (!array_key_exists($relationAttributeName, $relationAttributes)) {
                    return ResponseBuilder::fromUpdate($this->getUpdate())
                                          ->answerCallbackQuery()
                                          ->build();
                }
                $relationAttribute = $relationAttributes[$relationAttributeName];
                if ($v) {
                    $relationModel = call_user_func([$primaryRelation[2], 'findOne'], $v);
                } elseif ($text && ($field = ($relationAttribute[2] ?? null))) {
                    $relationQuery = call_user_func([$primaryRelation[2], 'find']);
                    $queryConditions = [];
                    if (is_array($field)) {
                        foreach ($field as $item) {
                            $queryConditions[$item] = $text;
                        }
                        $queryConditions['OR'] = $queryConditions;
                    } else {
                        $queryConditions[$field] = $text;
                    }
                    $relationModel = $relationQuery->where($queryConditions)->one();
                }
            }
            if (isset($relationModel)) {
                $relationData = $this->getIntermediateField($modelName, $attributeName, [[]]);
                $item = array_pop($relationData);
                if (empty($item)) {
                    foreach ($relationData as $key => $relationItem) {
                        if ($relationItem[$relationAttributeName] == $v) {
                            $item = $relationItem;
                            unset($relationData[$key]);
                            break;
                        }
                    }
                }
                $relationAttributesCount = count($relationAttributes);
                $isManyToOne = $relationAttributesCount == 1;
                $item[$relationAttributeName] = $relationModel->id;
                $relationData[] = $item;
                $relationData = array_filter(
                    $relationData,
                    function ($val) {
                        return $val;
                    }
                );
                $this->setIntermediateField($modelName, $attributeName, $relationData);

                $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $nextRelationAttributeName);

                if (!isset($nextRelationAttributeName) && $isManyToOne) {
                    $isValidRequest = true;
                }
            } else {
                $error = "not found";
            }
        }
        if ($isValidRequest) {
            $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
            $nextAttribute = $this->getNextKey($attributes, $attributeName);
            if (isset($nextAttribute) && !$isEdit) {
                return $this->generateResponse($modelName, $nextAttribute, compact('rule'));
            }

            return $this->save($rule);
        }

        return $this->generatePrivateResponse(
            $modelName,
            $attributeName,
            ['config' => $config, 'page' => $p, 'error' => $error]
        );
    }

    public function actionAddAttribute($a, $p = null)
    {
        $attributeName = $a;
        if (!$this->isRequestValid($attributeName)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->answerCallbackQuery()
                                  ->build();
        }

        $state = $this->getState();
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $rule = $this->getRule($modelName);
        $config = $rule['attributes'][$attributeName];
        if (!isset($p)) {
            $relation = $this->getRelation($config);
            $attributeNames = array_keys($relation['attributes']);
            $relationAttributeName = reset($attributeNames);
            $relationAttribute = $relation['attributes'][$relationAttributeName];
            if ($relationAttribute[0] == $modelClass) {
                $relationAttributeName = next($attributeNames);
            }
            $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $relationAttributeName);
            $attributeValue = $this->getIntermediateField($modelName, $attributeName, [[]]);
            $attributeLastItem = end($attributeValue);
            if (!empty($attributeLastItem)) {
                $attributeValue[] = [];
            }
            $this->setIntermediateField($modelName, $attributeName, $attributeValue);
        } else {
            $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
        }

        return $this->generatePrivateResponse(
            $this->getModelName($modelClass),
            $attributeName,
            ['config' => $config, 'page' => $p ?? 1]
        );
    }

    /**
     * @param string $m $this->getModelName(Model::class)
     * @param        $a
     * @param        $i
     * @param bool   $b back route
     *
     * @return array
     */
    public function actionEditAttribute($m, $a, $i, $b = false)
    {
        $id = $i;
        $backRoute = $b;
        $attributeName = $a;
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes) && array_key_exists($attributeName, $attributes) && isset($id)) {
            $this->beforeEdit($rule, $attributeName, $id);

            return $this->generateResponse($m, $attributeName, compact('rule', 'backRoute'));
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
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            $config = $attributes[$attributeName];

            $isAttributeRequired = $config['isRequired'] ?? true;
            if (!$isAttributeRequired) {
                $this->setIntermediateField($modelName, $attributeName, null);

                $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
                if ($isEdit) {
                    return $this->save($rule);
                }

                return $this->generateResponse($modelName, $attributeName, compact('rule'));
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->answerCallbackQuery()
                              ->build();
    }

    public function actionNextAttribute()
    {
        $state = $this->getState();
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
                if (isset($relationAttributeName)) {
                    $relationData = $this->getIntermediateField($modelName, $attributeName, [[]]);
                    $item = array_pop($relationData);
                    if (!empty($item[$relationAttributeName] ?? null)) {
                        $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                        $this->setIntermediateField(
                            $modelName,
                            self::FIELD_NAME_RELATION,
                            $nextRelationAttributeName
                        );

                        return $this->generatePrivateResponse(
                            $modelName,
                            $attributeName,
                            ['config' => $attributes[$attributeName]]
                        );
                    }

                    return ResponseBuilder::fromUpdate($this->getUpdate())
                                          ->answerCallbackQuery()
                                          ->build();
                }
            }
            $nextAttributeName = $this->getNextKey($attributes, $attributeName);
            $isAttributeRequired = $attributes[$attributeName]['isRequired'] ?? true;
            if (!$isAttributeRequired || !empty($this->getIntermediateField($modelName, $attributeName, null))) {
                if (isset($nextAttributeName) && !$isEdit) {
                    return $this->generateResponse($modelName, $nextAttributeName, compact('rule'));
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
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
                if (isset($relationAttributeName)) {
                    $nextRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                    $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $nextRelationAttributeName);

                    return $this->generatePrivateResponse(
                        $modelName,
                        $attributeName,
                        ['config' => $attributes[$attributeName]]
                    );
                }
            }
            $prevAttributeName = $this->getPrevKey($attributes, $attributeName);
            if (isset($prevAttributeName) && !$isEdit) {
                return $this->generateResponse($modelName, $prevAttributeName, compact('rule'));
            } else {
                $response = $this->onCancel($rule['model'], $this->getIntermediateField($modelName, 'id', null));
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
     *
     * @return array
     */
    public function actionRemoveAttribute($i)
    {
        $state = $this->getState();
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                [, $secondaryRelation] = $this->getRelationAttributes($modelClass, $relation);
                $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
                if (!isset($relationAttributeName)) {
                    $items = $this->getIntermediateField($modelName, $attributeName, []);
                    if (preg_match('|v_(\d+)|', $i, $match)) {
                        unset($items[$match[1]]);
                    } else {
                        foreach ($items as $key => $item) {
                            if ($item[$secondaryRelation[0]] == $i) {
                                unset($items[$key]);
                                break;
                            }
                        }
                    }
                    $this->setIntermediateField($modelName, $attributeName, $items);

                    return $this->generatePrivateResponse(
                        $modelName,
                        $attributeName,
                        ['config' => $attributes[$attributeName]]
                    );
                }
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->answerCallbackQuery()
                              ->build();
    }

    /**
     * @param $i int Item Primary Id
     *
     * @return array
     */
    public function actionEditRelationAttribute($i)
    {
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes($modelClass, $relation);
                $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
                if (!isset($relationAttributeName)) {
                    $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $primaryRelation[0]);
                    $items = $this->getIntermediateField($modelName, $attributeName, []);
                    foreach ($items as $key => $item) {
                        if ($item[$secondaryRelation[0]] == $i) {
                            unset($items[$key]);
                            $items[] = $item;
                            break;
                        }
                    }
                    $this->setIntermediateField($modelName, $attributeName, $items);

                    return $this->generatePrivateResponse(
                        $modelName,
                        $attributeName,
                        ['config' => $attributes[$attributeName]]
                    );
                }
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->answerCallbackQuery()
                              ->build();
    }

    /**
     * @param string $m  Model name $this->getModelName(Model::class)
     * @param int    $id Model id
     *
     * @return array
     */
    public function actionUpdate($m, $i, $b = '')
    {
        $id = $i;
        $rule = $this->getRule($m);
        $attributes = array_keys($rule['attributes']);

        /* @var ActiveRecord $model */
        $model = $this->getModel($rule, $id);
        $editButtons = array_map(
            function (string $attribute) use ($b, $id, $m, $model, $rule) {
                if (isset($rule['attributes'][$attribute]['hidden'])) {
                    return [];
                }

                return [
                    [
                        'text'          => Yii::t('bot', $model->getAttributeLabel($attribute)),
                        'callback_data' => self::createRoute(
                            'edit-attribute',
                            [
                                'i' => $id,
                                'm' => $m,
                                'a' => $attribute,
                                'b' => $b,
                            ]
                        ),
                    ],
                ];
            },
            $attributes
        );
        $editButtons = array_filter(
            $editButtons,
            function ($val) {
                return $val;
            }
        );
        $systemButtons = $this->getDefaultSystemButtons($b);
        $buttons = array_merge($editButtons, [$systemButtons]);
        $messageText = $this->render(
            "$m/show",
            [
                'model' => $model,
            ]
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->editMessageTextOrSendMessage(
                                  $messageText,
                                  $buttons,
                                  true
                              )
                              ->build();
    }

    /**
     * @param int $m  Model name
     * @param int $id Model id
     *
     * @return array
     */
    public function actionShow($m, $i)
    {
        $id = $i;
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
                                  $this->render(
                                      "$m/show",
                                      [
                                          'model' => $model,
                                      ]
                                  ),
                                  $keyboard ?? [],
                                  true
                              )
                              ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param bool         $isNew
     *
     * @return array
     */
    abstract protected function afterSave(ActiveRecord $model, bool $isNew);

    /**
     * @param string $className
     */
    protected function beforeCreate(string $className)
    {
        $this->setCurrentModelClass($className);
    }

    /**
     * @param array  $rule
     * @param string $attributeName
     * @param int    $id
     */
    protected function beforeEdit(array $rule, string $attributeName, int $id)
    {
        $state = $this->getState();
        $model = $this->getModel($rule, $id);
        if (isset($model)) {
            $this->setCurrentModelClass($rule['model']);
            $modelName = $this->getModelName($rule['model']);
            $this->setIntermediateField($modelName, 'id', $id);

            if ($this->isPrivateAttribute($attributeName, $rule)) {
                $relation = $this->getRelation($this->getAttributes($rule)[$attributeName]);
            }
            if (isset($relation) && count($relation['attributes']) > 1) {
                $relationModelClass = $relation['model'];
                $relationArrayKeys = array_keys($relation['attributes']);
                $relationAttributeName = reset($relationArrayKeys);
                $relationAttributeRefColumn = $relation['attributes'][$relationAttributeName][1];
                $relationModels = call_user_func(
                    [$relationModelClass, 'findAll'],
                    [$relationAttributeName => $model->getAttribute($relationAttributeRefColumn)]
                );
                $value = [];
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                /* @var ActiveRecord $relationModel */
                foreach ($relationModels as $relationModel) {
                    $relationItem = [];
                    foreach ($relationAttributes as $relationAttributeName => $relationAttribute) {
                        $relationItem[$relationAttributeName] = $relationModel->getAttribute($relationAttributeName);
                    }
                    $value[] = $relationItem;
                }
            } else {
                $value = $model->getAttribute($attributeName);
            }

            if (isset($value)) {
                $this->setIntermediateField($modelName, $attributeName, $value);
            }
        }
    }

    /**
     * @param ActiveRecord $model
     * @param bool         $isNew
     */
    protected function beforeSave(ActiveRecord $model, bool $isNew)
    {
    }

    /**
     * @param string $className
     *
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
     * @param string   $className
     * @param int|null $id
     *
     * @return array
     */
    protected function onCancel(string $className, ?int $id)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->answerCallbackQuery()
                              ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param string       $attributeName
     * @param              $attributeValue
     * @param array        $manyToManyRelationAttributes
     *
     * @return mixed
     */
    private function fillModel($model, $attributeName, $attributeValue, &$manyToManyRelationAttributes)
    {
        $state = $this->getState();
        $modelName = $this->getModelName($model::className());
        if ($state->isIntermediateFieldExists($this->createIntermediateFieldName($modelName, $attributeName))) {
            $relation = $this->getRelation($attributeValue);
            if (isset($relation)) {
                $relationAttributes = $relation['attributes'];
                if (count($relationAttributes) > 1) {
                    $manyToManyRelationAttributes[] = $attributeName;

                    return $model;
                }
                if (count($relation) == 1) {
                    $relationValue = $this->getIntermediateField($modelName, $attributeName, [[]]);
                    $model->setAttributes($relationValue[0]);
                }
            } else {
                $value = $this->getIntermediateField($modelName, $attributeName, null);
                $model->setAttribute($attributeName, $value ?? null);
            }
        }

        return $model;
    }

    /**
     * @param $model
     * @param $relation
     *
     * @return array [['column_id', 'ref_column_id', 'class'], ['sec_column_id','sec_ref_column_id', 'class', ?'field']]
     */
    private function getRelationAttributes($model, $relation)
    {
        $className = $model;
        if ($model instanceof ActiveRecord) {
            $className = $model::className();
        }
        $relationAttributes = $relation['attributes'];
        $primaryRelation = [];
        $secondaryRelation = [];
        foreach ($relationAttributes as $relationKey => $relationAttribute) {
            if (strcmp($className, $relationAttribute[0])) {
                $secondaryRelation = [];
                $secondaryRelation[] = $relationKey;
                $secondaryRelation[] = $relationAttribute[1];
                $secondaryRelation[] = $relationAttribute[0];
                if (isset($relationAttribute[2])) {
                    $secondaryRelation[] = $relationAttribute[2];
                }
            } else {
                $primaryRelation = [];
                $primaryRelation[] = $relationKey;
                $primaryRelation[] = $relationAttribute[1];
                $primaryRelation[] = $relationAttribute[0];
            }
        }

        if (!$primaryRelation) {
            $primaryRelation = $secondaryRelation;
            $secondaryRelation = [];
        }

        return [$primaryRelation, $secondaryRelation];
    }

    /**
     * @param string       $class
     * @param string|array $field
     * @param string       $value
     *
     * @return mixed|null
     */
    private function findOrCreateRelationModel($class, $field, $value)
    {
        $conditions = [];
        if (is_array($field)) {
            foreach ($field as $item) {
                $conditions[$item] = $value;
            }
            $conditions['OR'] = $conditions;
        } else {
            $conditions[$field] = $value;
        }
        $relationModel = call_user_func([$class, 'findOne'], $conditions);
        if (!$relationModel) {
            $relationModel = new $class($conditions);
            if (!$relationModel->save()) {
                return null;
            }
        }

        return $relationModel;
    }

    private function save(array $rule)
    {
        $state = $this->getState();
        $modelName = $this->getModelName($rule['model']);
        $id = $this->getIntermediateField($modelName, 'id', null);
        $isNew = is_null($id);
        /* @var ActiveRecord $model */
        if ($isNew) {
            $model = $this->createModel($rule);
        } else {
            $model = $this->getModel($rule, $id);
        }

        if ($model) {
            $manyToManyRelationAttributes = [];
            $behaviors = [];
            foreach ($rule['attributes'] as $attributeName => $attribute) {
                if (isset($attribute['behaviors'])) {
                    foreach ($attribute['behaviors'] as $behaviorName => $behaviorValue) {
                        $behaviors[$attributeName . $behaviorName] = $behaviorValue;
                    }
                }
                $component = $this->createAttributeComponent($attribute);
                if ($component instanceof FieldInterface) {
                    $fields = $component->getFields();
                    foreach ($fields as $field) {
                        $this->fillModel($model, $field, $attribute, $manyToManyRelationAttributes);
                    }
                }
                $this->fillModel($model, $attributeName, $attribute, $manyToManyRelationAttributes);
            }
            $model->attachBehaviors($behaviors);
            $this->beforeSave($model, $isNew);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    foreach ($manyToManyRelationAttributes as $attributeName) {
                        $relation = $this->getRelation($rule['attributes'][$attributeName]);
                        $relationModelClass = $relation['model'];

                        [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes($model, $relation);

                        $attributeValues = $this->getIntermediateField($modelName, $attributeName, []);
                        $secondaryAttributeIds = [];
                        foreach ($attributeValues as $attributeValue) {
                            if (!$attributeValue) {
                                continue;
                            }
                            if (!is_array($attributeValue)
                                && isset($secondaryRelation[3])
                                && ($relationModel = $this->findOrCreateRelationModel(
                                    $secondaryRelation[2],
                                    $secondaryRelation[3],
                                    $attributeValue
                                ))) {
                                $attributeValue = [];
                                $attributeValue[$secondaryRelation[0]] = $relationModel->id;
                            }
                            $conditions = [
                                $primaryRelation[0]   => $model->getAttribute(
                                    $primaryRelation[1]
                                ),
                                $secondaryRelation[0] => $attributeValue[$secondaryRelation[0]],
                            ];
                            /** @var ActiveRecord $relationModel */
                            $relationModel = call_user_func(
                                    [$relationModelClass, 'findOne'],
                                    $conditions
                                )
                                ?? Yii::createObject(
                                    [
                                        'class' => $relationModelClass,
                                    ]
                                );
                            $secondaryAttributeIds[] = $attributeValue[$secondaryRelation[0]];
                            $relationModel->setAttribute(
                                $primaryRelation[0],
                                $model->getAttribute($primaryRelation[1])
                            );
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
                            $itemsToDelete = $query->where(
                                ['not', [$secondaryRelation[0] => $secondaryAttributeIds]]
                            )->andWhere([$primaryRelation[0] => $model->id])->all();
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
     * @param       $element
     * @param bool  $ignoreWithBehavior
     *
     * @return mixed|null
     */
    private function getNextKey(array $assocArray, $element)
    {
        $keys = array_keys($assocArray);
        $nextKey = $keys[array_search($element, $keys) + 1] ?? null;
        if (isset($assocArray[$nextKey]['hidden'])) {
            $nextKey = $this->getNextKey($assocArray, $nextKey);
        }

        return $nextKey;
    }

    /**
     * @param array $assocArray
     * @param       $element
     * @param bool  $ignoreWithBehavior
     *
     * @return mixed|null
     */
    private function getPrevKey(array $assocArray, $element)
    {
        $keys = array_keys($assocArray);
        $prevKey = $keys[array_search($element, $keys) - 1] ?? null;
        if (isset($assocArray['hidden'])) {
            $prevKey = $this->getNextKey($assocArray, $prevKey);
        }

        return $prevKey;
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array  $options ['config' => [], 'page' => 1]
     *
     * @return array
     */
    private function generatePrivateResponse(string $modelName, string $attributeName, array $options)
    {
        $config = ArrayHelper::getValue($options, 'config', []);
        $page = ArrayHelper::getValue($options, 'page', 1);
        $backRoute = ArrayHelper::getValue($options, 'backRoute', false);
        $error = ArrayHelper::getValue($options, 'error', null);

        $state = $this->getState();
        $state->setName(
            self::createRoute(
                'set-attribute',
                [
                    'a' => $attributeName,
                    'p' => $page,
                ]
            )
        );
        $this->setIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
        $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $relation = $this->getRelation($config);
        if (isset($relationAttributeName)
            && ($relationAttribute = $relation['attributes'][$relationAttributeName])) {
            if (!strcmp($this->getModelName($relationAttribute[0]), $modelName)) {
                $rule = $this->getRule($modelName);
                $attributes = $this->getAttributes($rule);
                $nextAttribute = $this->getNextKey($attributes, $attributeName);

                if (isset($nextAttribute)) {
                    return $this->generateResponse($modelName, $nextAttribute, compact('rule', 'backRoute'));
                }
            }
            /* @var ActiveQuery $query */
            $query = call_user_func([$relationAttribute[0], 'find'], []);
            $valueAttribute = $relationAttribute[1];
            if (is_array($valueAttribute)) {
                $itemButtons = [];
            } else {
                $itemButtons = PaginationButtons::buildFromQuery(
                    $query,
                    function (int $page) use ($modelName, $attributeName) {
                        return self::createRoute(
                            'set-attribute',
                            [
                                'a' => $attributeName,
                                'p' => $page,
                            ]
                        );
                    },
                    function ($key, ActiveRecord $model) use ($modelName, $attributeName, $valueAttribute) {
                        return [
                            'text'          => $this->getLabel($model),
                            'callback_data' => self::createRoute(
                                'set-attribute',
                                [
                                    'a' => $attributeName,
                                    'v' => $model->getAttribute($valueAttribute),
                                ]
                            ),
                        ];
                    },
                    $page
                );
            }
            $attributeValue = $this->getIntermediateField($modelName, $attributeName, []);
            if (!empty($attributeValue) && !isset($attributeValue[0])) {
                $item = $attributeValue[0];
                $relationAttributeValue = $item[$relationAttributeName];
                $currentValue = $query->where([$valueAttribute => $relationAttributeValue])->one();
            }
            $systemButtons = $this->generateSystemButtons(
                $modelName,
                $attributeName,
                ['isEmpty' => isset($attributeValue), 'backRoute' => $backRoute]
            );
            $buttons = array_merge($itemButtons, [$systemButtons]);

            return ResponseBuilder::fromUpdate($this->getUpdate())
                                  ->editMessageTextOrSendMessage(
                                      $this->render(
                                          "$modelName/$attributeName/set-$relationAttributeName",
                                          [
                                              'currentValue' => $currentValue ?? null,
                                              'step'         => $step,
                                              'error'        => $error,
                                              'totalSteps'   => $totalSteps,
                                              'isEdit'       => $isEdit,
                                          ]
                                      ),
                                      $buttons,
                                      true
                                  )
                                  ->build();
        }

        $isAttributeRequired = $config['isRequired'] ?? true;
        $relationAttributesWithoutPrimaryAttribute = $relation['attributes'];
        array_shift($relationAttributesWithoutPrimaryAttribute);
        $relationAttributesWithoutPrimaryAttributeKeys = array_keys($relationAttributesWithoutPrimaryAttribute);
        $relationAttributeName = reset($relationAttributesWithoutPrimaryAttributeKeys);
        if ($relationAttributeName
            && !strcmp(
                $modelName,
                $this->getModelName($relationAttributesWithoutPrimaryAttribute[$relationAttributeName][0])
            )) {
            $relationAttributeName = array_key_first($relation['attributes']);
        }
        $items = $this->getIntermediateField($modelName, $attributeName, []);
        $itemButtons = PaginationButtons::buildFromArray(
            $items,
            function (int $page) use ($attributeName) {
                return self::createRoute(
                    'add-attribute',
                    [
                        'a' => $attributeName,
                        'p' => $page,
                    ]
                );
            },
            function ($key, $item) use ($relation, $relationAttributeName, $isAttributeRequired, $items) {
                try {
                    /* @var ActiveRecord $model */
                    $model = Yii::createObject(array_merge(['class' => $relation['model']], $item));
                    $label = $this->getLabel($model);
                    $id = $item[$relationAttributeName];
                } catch (\Exception $ex) {
                    $label = $item;
                    $id = 'v_' . $key;
                }

                return array_merge(
                    [
                        [
                            'text'          => $label,
                            'callback_data' => self::createRoute(
                                'edit-relation-attribute',
                                [
                                    'i' => $id,
                                ]
                            ),
                        ],
                    ],
                    (count($items) == 1 && $isAttributeRequired) ? [] : [
                        [
                            'text'          => Emoji::DELETE,
                            'callback_data' => self::createRoute(
                                'remove-attribute',
                                [
                                    'i' => $id,
                                ]
                            ),
                        ],
                    ]
                );
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->editMessageTextOrSendMessage(
                                  $this->render(
                                      "$modelName/set-$attributeName",
                                      [
                                          'step'       => $step,
                                          'error'      => $error,
                                          'totalSteps' => $totalSteps,
                                          'isEdit'     => $isEdit,
                                      ]
                                  ),
                                  array_merge(
                                      $itemButtons,
                                      [
                                          $this->generateSystemButtons(
                                              $modelName,
                                              $attributeName,
                                              ['isEmpty' => empty($items), 'backRoute' => $backRoute]
                                          ),
                                      ]
                                  )
                              )
                              ->build();
    }

    private function generatePublicResponse(
        string $modelName,
        string $attributeName,
        array $options
    ) {
        $backRoute = ArrayHelper::getValue($options, 'backRoute', false);
        $config = ArrayHelper::getValue($options, 'config', []);
        $error = ArrayHelper::getValue($options, 'error', null);
        $state = $this->getState();
        $state->setName(
            self::createRoute(
                'enter-attribute',
                [
                    'a' => $attributeName,
                ]
            )
        );
        $this->setIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
        $attributeValue = $this->getIntermediateField($modelName, $attributeName, null);
        $systemButtons = $this->generateSystemButtons(
            $modelName,
            $attributeName,
            ['isEmpty' => empty($attributeValue), 'backRoute' => $backRoute]
        );
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));

        return ResponseBuilder::fromUpdate($this->getUpdate())
                              ->editMessageTextOrSendMessage(
                                  $this->render(
                                      "$modelName/set-$attributeName",
                                      [
                                          'currentValue' => $attributeValue,
                                          'error'        => $error,
                                          'step'         => $step,
                                          'totalSteps'   => $totalSteps,
                                          'isEdit'       => $isEdit,
                                          'model'        => null,
                                      ]
                                  ),
                                  [$systemButtons],
                                  true
                              )
                              ->build();
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array  $options
     *
     * @return array
     */
    private function generateResponse(string $modelName, string $attributeName, array $options)
    {
        $rule = ArrayHelper::getValue($options, 'rule', []);
        $backRoute = ArrayHelper::getValue($options, 'backRoute', false);
        $config = $rule['attributes'][$attributeName];
        if ($this->isPrivateAttribute($attributeName, $rule)) {
            $attributes = $config['relation']['attributes'];
            $this->setIntermediateField(
                $modelName,
                self::FIELD_NAME_RELATION,
                (count($attributes) == 1) ? array_keys($attributes)[0] : null
            );

            return $this->generatePrivateResponse($modelName, $attributeName, compact('config', 'backRoute'));
        } else {
            return $this->generatePublicResponse($modelName, $attributeName, compact('config', 'backRoute'));
        }
    }

    /**
     * @param bool $backRoute
     *
     * @return array
     */
    private function getDefaultSystemButtons($backRoute = false)
    {
        if ($backRoute) {
            $backRoute = $this->backRoute->get();
        } else {
            $backRoute = self::createRoute('prev-attribute');
        }
        $systemButtons = [];
        /* 'Back' button */
        $systemButtons[] = [
            'text'          => Emoji::BACK,
            'callback_data' => $backRoute,
        ];
        /* 'Menu' button */
        $systemButtons[] = [
            'callback_data' => MenuController::createRoute(),
            'text'          => Emoji::MENU,
        ];

        return $systemButtons;
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array  $options
     *
     * @return array
     */
    private function generateSystemButtons(string $modelName, string $attributeName, array $options)
    {
        $isEmpty = ArrayHelper::getValue($options, 'isEmpty', false);
        $backRoute = ArrayHelper::getValue($options, 'backRoute', false);
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $systemButtons = $this->getDefaultSystemButtons($backRoute);

        $isEdit = !is_null($this->getIntermediateField($modelName, 'id', null));
        $isAttributeRequired = $config['isRequired'] ?? true;
        $relation = $this->getRelation($config);
        $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);

        if (!isset($relation) || count($relation['attributes']) == 1) {
            /* 'Clear' button */
            if (!$isAttributeRequired && !$isEmpty) {
                $systemButtons[] = [
                    'text'          => Emoji::DELETE,
                    'callback_data' => self::createRoute('clear-attribute'),
                ];
            }
        } else {
            /* 'Add' button */
            $systemButtons[] = [
                'text'          => Emoji::ADD,
                'callback_data' => self::createRoute(
                    'add-attribute',
                    [
                        'a' => $attributeName,
                    ]
                ),
            ];
        }

        /* 'Next' button */
        if (((!$isAttributeRequired || !$isEmpty) && (!$isEdit || (isset($relation) && count(
                        $relation['attributes']
                    ) > 1)
                && !isset($relationAttributeName)))) {
            $nextAttribute = $this->getNextKey($attributes, $attributeName);
            $systemButtons[] = [
                'text'          => Yii::t('bot', (isset($nextAttribute) && !$isEdit) ? 'Next' : 'Finish'),
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

        return [$step, $totalSteps];
    }

    private function isPrivateAttribute(string $attributeName, array $rule)
    {
        $config = $rule['attributes'][$attributeName];

        return array_key_exists('relation', $config);
    }

    /**
     * @param array $rule
     *
     * @return object|null
     */
    private function createModel(array $rule)
    {
        if (!array_key_exists('model', $rule)) {
            Yii::warning('Rule must contain the \'model\' key');

            return null;
        }
        try {
            $object = Yii::createObject(['class' => $rule['model']]);
            if ($object instanceof ActiveRecord) {
                return $object;
            }
            Yii::warning(
                'The \'model\' key must contain a name of the class that is inherited from ' . ActiveRecord::class
            );

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param array $rule
     * @param int   $id
     *
     * @return ActiveRecord|null
     */
    private function getModel(array $rule, int $id)
    {
        $modelName = $this->getModelName($rule['model']);
        $getModelMethodName = 'get' . ucfirst($modelName);
        if (method_exists($this, $getModelMethodName)) {
            /* @var ActiveRecord $model */
            $model = call_user_func([$this, $getModelMethodName], $id);
        } else {
            $model = call_user_func([$rule['model'], 'findOne'], $id);
        }

        return $model ?? null;
    }

    /**
     * @param $modelName
     * @param $model
     *
     * @return array
     */
    private function getKeyboard($modelName, $model)
    {
        $getKeyboardMethodName = 'get' . ucfirst($modelName) . 'Keyboard';
        if (method_exists($this, $getKeyboardMethodName)) {
            $keyboard = call_user_func([$this, $getKeyboardMethodName], $model);
        }

        return $keyboard ?? null;
    }

    /**
     * @param string $modelName
     *
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
     *
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
     *
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
        if (isset($relation) && array_key_exists('attributes', $relation)) {
            $attributes = $relation['attributes'];
        }
        if (!empty($attributes)) {
            $attributesCount = count($attributes);
            if ($attributesCount <= 1 && array_key_exists('model', $relation)) {
                Yii::warning(
                    'When using many-to-many relationship, \'model\' can`t be empty and count of attributes must be greater than 1.'
                );

                return null;
            }
            if ($attributesCount != 1 && !array_key_exists('model', $relation)) {
                Yii::warning(
                    'When using many-to-one relationship, \'model\' must be empty and count of attributes must be equal to one.'
                );

                return null;
            }
            foreach ($attributes as $attribute => $config) {
                if (count($config) < 2) {
                    Yii::warning(
                        "Error occurred when reading '"
                        . serialize($config)
                        . "' attribute: its value must be an array with model name in 0th index and ref column in 1th index"
                    );

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
                if (is_array($refColumn)) {
                    foreach ($refColumn as $value) {
                        if (is_array($value)) {
                            $value = array_key_first($value);
                        }
                        if (!$model->hasAttribute($value)) {
                            Yii::warning("$modelClassName doesn't have $refColumn attribute");

                            return null;
                        }
                    }
                } elseif (!$model->hasAttribute($refColumn)) {
                    Yii::warning("$modelClassName doesn't have $refColumn attribute");

                    return null;
                }
            }

            return $relation;
        }

        return null;
    }

    /**
     * Сначала ищем метод в контроллере, затем в модели
     *
     * @param ActiveRecord $model
     *
     * @return mixed
     */
    private function getLabel(ActiveRecord $model)
    {
        $methodName = 'get' . ucfirst($this->getModelName(get_class($model))) . 'Label';
        if (method_exists($this, $methodName)) {
            return $this->$methodName($model);
        } else {
            $methodName = $this->getModelName(get_class($model)) . 'Label';

            return $model->$methodName;
        }
    }

    private function canSkipAttribute(array $rule, string $attributeName)
    {
        $attributes = $this->getAttributes($rule);
        $modelName = $this->getModelName($rule['model']);
        $config = $attributes[$attributeName];
        $isRequired = $config['isRequired'] ?? true;
        $isEmptyAttribute = empty($this->getIntermediateField($modelName, $attributeName, null));

        return !$isRequired || !$isEmptyAttribute;
    }

    /**
     * @param string $attributeName
     *
     * @return bool
     */
    private function isRequestValid(string $attributeName)
    {
        $state = $this->getState();
        $modelClass = $this->getCurrentModelClass();
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
            if ($this->canSkipAttribute($rule, $stateAttributeName)
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
