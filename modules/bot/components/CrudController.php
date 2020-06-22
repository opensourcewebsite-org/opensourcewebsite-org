<?php

namespace app\modules\bot\components;

use app\components\helpers\ArrayHelper;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\request\Request;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\rules\FieldInterface;
use app\modules\bot\services\AttributeButtonsService;
use app\modules\bot\services\EndRouteService;
use app\modules\bot\services\ViewFileService;
use Exception;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

/**
 * Class CrudController
 *
 * @package app\modules\bot\components
 */
abstract class CrudController extends Controller
{
    const FIELD_NAME_RELATION = 'relationAttributeName';
    const FIELD_NAME_MODEL_CLASS = 'modelClass';
    const FIELD_NAME_ATTRIBUTE = 'attributeName';
    const FIELD_NAME_ID = 'id';
    public const SAFE_ATTRIBUTE = 'vacanciesCompanyId';

    /** @var EndRouteService */
    public $endRoute;
    /** @var AttributeButtonsService */
    public $attributeButtons;
    /** @var ViewFileService */
    public $viewFile;
    /**
     * @var array|mixed
     */
    private $manyToManyRelationAttributes;

    /** @inheritDoc */
    public function __construct($id, $module, $config = [])
    {
        $this->endRoute = Yii::createObject(
            [
                'class' => EndRouteService::class,
                'state' => $module->userState,
                'controller' => $this,
            ]
        );
        $this->attributeButtons = Yii::createObject(
            [
                'class' => AttributeButtonsService::class,
                'controller' => $this,
            ]
        );
        $this->viewFile = Yii::createObject(
            [
                'class' => ViewFileService::class,
                'controller' => $this,
            ]
        );
        parent::__construct($id, $module, $config);
    }

    /** @inheritDoc */
    public function bindActionParams($action, $params)
    {
        if (!method_exists(self::class, $action->actionMethod)) {
            $this->endRoute->make($action->id, $params);
            foreach ($this->rules() as $rule) {
                $this->setIntermediateField($this->getModelName($rule['model']), self::FIELD_NAME_ID, null);
            }
        }

        return parent::bindActionParams($action, $params);
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
        $this->resetFields();

        if (!empty($attributes)) {
            $this->beforeCreate($this->getModelClassByRule($rule));
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
     * @param $config
     *
     * @return FieldInterface|null
     * @throws InvalidConfigException
     */
    private function createAttributeComponent($config)
    {
        if (isset($config['component'])) {
            $component = $config['component'];
            $objectParams = [];
            if (is_array($component) && isset($component['class'])) {
                $objectParams['class'] = $component['class'];
                $objectParams = array_merge($objectParams, $component['attributes'] ?? []);
            } else {
                $objectParams['class'] = $component;
            }
            /** @var FieldInterface $object */
            $object = Yii::createObject($objectParams, [$this, $config]);

            return $object;
        }

        return null;
    }

    /**
     * @param string $modelName
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
     * @param string $modelName $this->getModelName()
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
     * @param null $defaultValue
     *
     * @return mixed|null
     */
    private function getIntermediateField($modelName, $attributeName, $defaultValue = null)
    {
        $name = $this->createIntermediateFieldName($modelName, $attributeName);

        return $this->getState()->getIntermediateField($name, $defaultValue);
    }

    /**
     * @param string $modelName
     * @param array $values
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

    /**
     * Enter Attribute
     *
     * @param      $a
     * @param null $text
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionEnA($a, $text = null)
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

        if ($this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];

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
            $isNotValid = !$model->validate(array_keys($fieldResult));
        } else {
            $model->setAttribute($attributeName, $fieldResult);
            $isNotValid = !$model->validate($attributeName);
        }
        if ($isNotValid) {
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
        if (!$fieldResult && (!is_string($text) || $text === '')) {
            return $this->generatePublicResponse($modelName, $attributeName, compact('config'));
        }

        $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
        $nextAttribute = $this->getNextKey($attributes, $attributeName);

        if (isset($nextAttribute) && !$isEdit) {
            return $this->generateResponse($modelName, $nextAttribute, compact('rule'));
        }

        return $this->save($rule);
    }

    /**
     * Set Attribute
     *
     * @param string $a Attribute name
     * @param int $p Page number
     * @param null $v Attribute value
     * @param null $text User Message
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionSA($a, $p = 1, $v = null, $text = null)
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

        if (!$this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
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
                $relationData = array_values($relationData);
                $this->setIntermediateField($modelName, $attributeName, $relationData);
            } else {
                if (!array_key_exists($relationAttributeName, $relationAttributes)) {
                    return ResponseBuilder::fromUpdate($this->getUpdate())
                        ->answerCallbackQuery()
                        ->build();
                }
                $relationAttribute = $relationAttributes[$relationAttributeName];
                if ($v) {
                    if ($secondaryRelation) {
                        $relationModel = call_user_func([$secondaryRelation[2], 'findOne'], $v);
                    } else {
                        $relationModel = call_user_func([$primaryRelation[2], 'findOne'], $v);
                    }
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
                        if (!is_array($relationItem)) {
                            continue;
                        }
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
                if ($isManyToOne && ($modelField = array_key_first($relationAttributes))) {
                    $this->setIntermediateField($modelName, $modelField, $relationModel->id);
                }
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
            $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
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

    /**
     * Add Attribute
     *
     * @param      $a
     * @param null $p
     *
     * @return array
     */
    public function actionAA($a, $p = null)
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
        $config = $rule['attributes'][$attributeName];
        if (!isset($p)) {
            $relation = $this->getRelation($config);
            [, $secondaryRelation] = $this->getRelationAttributes($modelClass, $relation);
            $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $secondaryRelation[0]);
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
     * Edit Attribute
     *
     * @param string $m $this->getModelName(Model::class)
     * @param        $a
     * @param        $i
     * @param bool $b back route
     *
     * @return array
     */
    public function actionEA($m, $a, $i, $b = false)
    {
        $id = $i;
        $enableBackRoute = $b;
        $attributeName = $a;
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes) && array_key_exists($attributeName, $attributes) && isset($id)) {
            $this->beforeEdit($rule, $attributeName, $id);

            return $this->generateResponse($m, $attributeName, compact('rule', 'enableBackRoute'));
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * Button Callback
     *
     * @param $a
     * @param $i
     *
     * @return array
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionBC($a, $i = 0)
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
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $id = $this->getIntermediateField($modelName, self::FIELD_NAME_ID);
        $model = $this->getFilledModel($rule);
        /** @var ActiveRecord $model */
        $model = call_user_func($config['buttons'][$i]['callback'], $model);
        $this->setIntermediateFields($modelName, $model->getAttributes());

        $nextAttribute = $this->getNextKey($attributes, $attributeName);
        if (isset($nextAttribute) && !$id) {
            return $this->generateResponse($modelName, $nextAttribute, compact('rule'));
        }

        return $this->save($rule);
    }

    /**
     * Clear Attribute
     *
     * @return array
     */
    public function actionCA()
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

                $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
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

    /**
     * Show Attribute
     *
     * @param $a
     *
     * @return array
     */
    public function actionShA($a)
    {
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $a;
        $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
            $relationAttributes = $relation['attributes'];
            array_shift($relationAttributes);
            $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
            if (isset($relationAttributeName)) {
                $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);

                return $this->generatePrivateResponse(
                    $modelName,
                    $attributeName,
                    ['config' => $attributes[$attributeName]]
                );
            }
        }
        if (!$isEdit) {
            return $this->generateResponse($modelName, $attributeName, compact('rule'));
        } else {
            $response = $this->onCancel(
                $this->getModelClassByRule($rule),
                $this->getIntermediateField($modelName, self::FIELD_NAME_ID, null)
            );
            $this->resetFields();

            return $response;
        }
    }

    public function resetFields()
    {
        $endRoute = $this->endRoute->get();
        $safeAttribute = $this->getState()->getIntermediateField(self::SAFE_ATTRIBUTE);
        $this->getState()->reset();
        $this->getState()->setIntermediateField(self::SAFE_ATTRIBUTE, $safeAttribute);
        $this->endRoute->set($endRoute);
    }

    /**
     * Next Attribute
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionNA()
    {
        $modelClass = $this->getCurrentModelClass();
        if (!$modelClass) {
            throw new BadRequestHttpException();
        }
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
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

    /**
     * Previous Attribute
     *
     * @return array
     */
    public function actionPA()
    {
        $modelClass = $this->getCurrentModelClass();
        $modelName = $this->getModelName($modelClass);
        $attributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
                if (isset($relationAttributeName)) {
                    $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                    $this->setIntermediateField($modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);

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
                $response = $this->onCancel(
                    $this->getModelClassByRule($rule),
                    $this->getIntermediateField($modelName, self::FIELD_NAME_ID, null)
                );
                $this->resetFields();

                return $response;
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * Remove Attribute
     *
     * @param $i int Item Primary Id
     *
     * @return array
     */
    public function actionRA($i)
    {
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
                    $items = array_values($items);
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
     * Edit Relation Attribute
     *
     * @param $i int Item Primary Id
     *
     * @return array
     */
    public function actionERA($i)
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
     * Action Update
     *
     * @param string $m Model name $this->getModelName(Model::class)
     * @param int $id Model id
     *
     * @return array
     */
    public function actionU($m, $i, $b = '')
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
                        'text' => Yii::t('bot', $model->getAttributeLabel($attribute)),
                        'callback_data' => self::createRoute(
                            'e-a',
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
        $this->setCurrentModelClass($this->getModelClassByRule($rule));
        $systemButtons = $this->getDefaultSystemButtons($b);
        $systemButtons = array_values($systemButtons);
        $buttons = array_merge($editButtons, [$systemButtons]);
        $params = [
            'model' => $model,
        ];
        $messageText = $this->render(
            ($rule['view'] ?? null) ?: 'show',
            $this->prepareViewParams($params, $rule, null)
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
     * @param array $params
     * @param array $rule
     * @param string $attributeName
     *
     * @return array
     */
    private function prepareViewParams($params, $rule, $attributeName)
    {
        if ($attributeName) {
            $callbackFunction = $rule['attributes'][$attributeName]['prepareViewParams'] ?? null;
        } else {
            $callbackFunction = $rule['prepareViewParams'] ?? null;
        }
        if ($callbackFunction) {
            $params = call_user_func($callbackFunction, $params);
        }

        return $params;
    }

    /**
     * @param string $view
     * @param array $params
     * @param array $options
     *
     * @return helpers\MessageText
     */
    private function renderAttribute($view, $params, $options)
    {
        $attributeName = ArrayHelper::getValue($options, 'attributeName', null);
        $rule = ArrayHelper::getValue($options, 'rule', null);
        $model = $params['model'];
        if ($attributeName && $model->hasProperty($attributeName)) {
            $params['currentValue'] = $model->$attributeName;
        }

        return $this->render($view, $this->prepareViewParams($params, $rule, $attributeName));
    }

    /**
     * Action Show
     *
     * @param int $m Model name
     * @param int $id Model id
     *
     * @return array
     */
    public function actionSh($m, $i)
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
                $this->renderAttribute(
                    "$m/" . (($rule['view'] ?? null) ?: 'show'),
                    [
                        'model' => $model,
                    ],
                    compact('rule')
                ),
                $keyboard ?: [],
                true
            )
            ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param bool $isNew
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
     * @param array $rule
     * @param string $attributeName
     * @param int $id
     */
    protected function beforeEdit(array $rule, string $attributeName, int $id)
    {
        $model = $this->getModel($rule, $id);
        if (isset($model)) {
            $this->setCurrentModelClass($this->getModelClassByRule($rule));
            $modelName = $this->getModelName($this->getModelClassByRule($rule));
            $this->setIntermediateField($modelName, self::FIELD_NAME_ID, $id);

            if ($this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
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
     * @param bool $isNew
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
     * @param string $className
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
     * @param string $attributeName
     * @param              $attributeValue
     * @param array $manyToManyRelationAttributes
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
     * @param string|ActiveRecord $mainModel Model::class or Model
     * @param                     $relation
     *
     * @return array [['column_id', 'ref_column_id', 'class'], ['sec_column_id','sec_ref_column_id', 'class', ?'field']]
     */
    private function getRelationAttributes($mainModel, $relation)
    {
        $className = $mainModel;
        if ($mainModel instanceof ActiveRecord) {
            $className = $mainModel::className();
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
     * @param string $class
     * @param string|array $field
     * @param string $value
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

    /**
     * @param array $rule
     *
     * @return array
     */
    private function getRuleBehaviors($rule)
    {
        $behaviors = [];
        foreach ($rule['attributes'] as $attributeName => $attribute) {
            if (isset($attribute['behaviors'])) {
                foreach ($attribute['behaviors'] as $behaviorName => $behaviorValue) {
                    $behaviors[$attributeName . $behaviorName] = $behaviorValue;
                }
            }
        }

        return $behaviors;
    }

    /**
     * @param array $rule
     *
     * @return ActiveRecord
     * @throws InvalidConfigException
     */
    private function getFilledModel($rule)
    {
        $modelName = $this->getModelName($this->getModelClassByRule($rule));
        $id = $this->getIntermediateField($modelName, self::FIELD_NAME_ID, null);
        $isNew = is_null($id);
        $manyToManyRelationAttributes = [];
        /* @var ActiveRecord $model */
        if ($isNew) {
            $model = $this->createModel($rule);
            foreach ($rule['attributes'] as $attributeName => $attribute) {
                $component = $this->createAttributeComponent($attribute);
                if ($component instanceof FieldInterface) {
                    $fields = $component->getFields();
                    foreach ($fields as $field) {
                        $this->fillModel($model, $field, $attribute, $manyToManyRelationAttributes);
                    }
                }
                $this->fillModel($model, $attributeName, $attribute, $manyToManyRelationAttributes);
            }
            $model->attachBehaviors($this->getRuleBehaviors($rule));
        } else {
            $model = $this->getModel($rule, $id);
        }
        $this->manyToManyRelationAttributes = $manyToManyRelationAttributes;

        return $model;
    }

    /**
     * @param ActiveRecord $model
     * @param array $config
     *
     * @return ActiveRecord|null
     * @throws InvalidConfigException
     */
    public function createRelationModel($model, $config)
    {
        $relation = $config['relation'] ?? [];
        if ($relation) {
            [
                $primaryRelation, $secondaryRelation,
            ] = $this->getRelationAttributes($model, $relation);
            $secondaryFieldData = null;
            if (new $secondaryRelation[2] instanceof DynamicModel) {
                $component = $this->createAttributeComponent($relation);
                $secondaryFieldData = $component->prepare('');
            }
            /** @var ActiveRecord $relationModel */
            $relationModel = new $relation['model'];
            $relationModel->setAttributes([
                $primaryRelation[0] => $model->id,
                $secondaryRelation[0] => $secondaryFieldData,
            ]);

            return $relationModel;
        }

        return null;
    }

    /**
     * @param array $rule
     *
     * @return array
     * @throws InvalidConfigException
     * @throws Throwable
     */
    private function save(array $rule)
    {
        $modelName = $this->getModelName($this->getModelClassByRule($rule));
        $id = $this->getIntermediateField($modelName, self::FIELD_NAME_ID, null);
        $isNew = is_null($id);
        $model = $this->getFilledModel($rule);
        if ($model->validate()) {
            $this->beforeSave($model, $isNew);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $relationModel = $this->createRelationModel($model, $rule);
                    if ($relationModel && !$relationModel->save()) {
                        throw new Exception("not possible to save "
                            . $relationModel->formName() . " because " . serialize($relationModel->getErrors()));
                    }
                    foreach ($this->manyToManyRelationAttributes as $attributeName) {
                        $relation = $this->getRelation($rule['attributes'][$attributeName]);
                        $relationModelClass = $relation['model'];

                        [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes($model, $relation);

                        $attributeValues = $this->getIntermediateField($modelName, $attributeName, []);
                        $secondaryAttributeIds = [];
                        foreach ($attributeValues as $attributeValue) {
                            if (!$attributeValue) {
                                continue;
                            }
                            if (new $secondaryRelation[2] instanceof DynamicModel) {
                                if (!is_array($attributeValue)) {
                                    $text = $attributeValue;
                                    $attributeValue = [];
                                    $attributeValue[$secondaryRelation[0]] = $text;
                                }
                            } elseif (!is_array($attributeValue)
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
                                $primaryRelation[0] => $model->getAttribute(
                                    $primaryRelation[1]
                                ),
                                $secondaryRelation[0] => $attributeValue[$secondaryRelation[0]],
                            ];
                            /** @var ActiveRecord $relationModel */
                            $relationModel = call_user_func(
                                [$relationModelClass, 'findOne'],
                                $conditions
                            );
                            if (!$relationModel) {
                                $relationModel = Yii::createObject(
                                    [
                                        'class' => $relationModelClass,
                                    ]
                                );
                            }
                            $secondaryAttributeIds[] = $attributeValue[$secondaryRelation[0]];
                            $relationModel->setAttribute(
                                $primaryRelation[0],
                                $model->getAttribute($primaryRelation[1])
                            );
                            $relationModel->setAttributes($attributeValue);
                            if (!$relationModel->save()) {
                                throw new Exception("not possible to save "
                                    . $relationModel->formName() . " because " . serialize($relationModel->getErrors()));
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
                    $this->getState()->reset();
                    $transaction->commit();

                    return $this->afterSave($model, $isNew);
                }
            } catch (Exception $ex) {
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
     *
     * @return mixed|null
     */
    private function getPrevKey(array $assocArray, $element)
    {
        $keys = array_keys($assocArray);
        $prevKey = $keys[array_search($element, $keys) - 1] ?? null;
        if (isset($assocArray[$prevKey]['hidden'])) {
            $prevKey = $this->getPrevKey($assocArray, $prevKey);
        }

        return $prevKey;
    }

    /**
     * @param array $buttons
     * @param array $systemButtons
     * @param array $options
     *
     * @return array
     */
    public function prepareButtons($buttons, $systemButtons, $options = [])
    {
        $modelName = ArrayHelper::getValue($options, 'modelName', null);
        $isEmpty = ArrayHelper::getValue($options, 'isEmpty', true);
        $attributeName = ArrayHelper::getValue($options, self::FIELD_NAME_ATTRIBUTE, null);
        $config = ArrayHelper::getValue($options, 'config', []);

        $isAttributeRequired = $config['isRequired'] ?? true;
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
        $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
        $configButtons = $this->attributeButtons->get($rule, $attributeName);
        if ($configButtons) {
            $buttons = array_merge($buttons, [$configButtons]);
        }
        /* 'Next' button */
        if (!isset($relationAttributeName) && (!$isAttributeRequired || !$isEmpty)
//                || ((isset($relation) && count($relation['attributes']) > 1)))
        ) {
            $buttonSkip = $config['buttonSkip'] ?? [];

            $buttonSkip = ArrayHelper::merge(
                [
                    'text' => Yii::t('bot', $isEdit ? 'No' : 'Skip'),
                    'callback_data' => self::createRoute('n-a'),
                ],
                $buttonSkip
            );

            $buttons[] = [$buttonSkip];
        }

        return array_merge($buttons, [$systemButtons]);
    }

    /**
     * Now work only for many to many
     *
     * @param array $rule
     * @param array $relation
     * @param integer $id
     * @param integer $editableFieldId
     *
     * @return ActiveRecord
     * @throws Exception
     */
    private function getModelByRelation($rule, $relation, $id, $editableFieldId = null)
    {
        [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes(
            $this->getModelClassByRule($rule),
            $relation
        );
        $modelClass = $relation['model'] ?? null;
        $conditions = [];
        if ($editableFieldId) {
            $conditions[$primaryRelation[0]] = $editableFieldId;
            $conditions[$secondaryRelation[0]] = $id;
        } else {
            $conditions[$secondaryRelation[1]] = $id;
            $modelClass = $secondaryRelation[2];
        }
        $model = call_user_func([$modelClass, 'findOne'], $conditions);
        /* @var ActiveRecord $model */
        if (!$model) {
            throw new Exception($modelClass . ' with params ' . serialize($conditions) . ' was not found');
        }

        return $model;
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array $options ['config' => [], 'page' => 1]
     *
     * @return array
     */
    private function generatePrivateResponse(string $modelName, string $attributeName, array $options)
    {
        $config = ArrayHelper::getValue($options, 'config', []);
        $page = ArrayHelper::getValue($options, 'page', 1);
        $enableBackRoute = ArrayHelper::getValue($options, 'enableBackRoute', false);
        $error = ArrayHelper::getValue($options, 'error', null);
        $rule = $this->getRule($modelName);

        $state = $this->getState();
        $state->setName(
            self::createRoute(
                's-a',
                [
                    'a' => $attributeName,
                    'p' => $page,
                ]
            )
        );
        $this->setIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $relationAttributeName = $this->getIntermediateField($modelName, self::FIELD_NAME_RELATION, null);
        $editableFieldId = $this->getIntermediateField($modelName, self::FIELD_NAME_ID, null);
        $isEdit = !is_null($editableFieldId);
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $relation = $this->getRelation($config);
        if (isset($relationAttributeName)
            && ($relationAttribute = $relation['attributes'][$relationAttributeName])) {
            if (!strcmp($this->getModelName($relationAttribute[0]), $modelName)) {
                $attributes = $this->getAttributes($rule);
                $nextAttribute = $this->getNextKey($attributes, $attributeName);

                if (isset($nextAttribute)) {
                    return $this->generateResponse($modelName, $nextAttribute, compact('rule', 'enableBackRoute'));
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
                    function (int $page) use ($attributeName) {
                        return self::createRoute(
                            's-a',
                            [
                                'a' => $attributeName,
                                'p' => $page,
                            ]
                        );
                    },
                    function ($key, ActiveRecord $model) use ($attributeName, $valueAttribute) {
                        return [
                            'text' => $this->getLabel($model),
                            'callback_data' => self::createRoute(
                                's-a',
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
            $isEmpty = empty($attributeValue);
            $systemButtons = $this->generateSystemButtons(
                $modelName,
                $attributeName,
                compact('isEmpty', 'enableBackRoute')
            );
            $buttons = $this->prepareButtons(
                $itemButtons,
                $systemButtons,
                compact('config', 'isEmpty', 'modelName', self::FIELD_NAME_ATTRIBUTE)
            );
            $model = $this->getFilledModel($rule);

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->renderAttribute(
                        $this->prepareViewFileName($rule, $attributeName, compact('relationAttributeName')),
                        [
                            'step' => $step,
                            'error' => $error,
                            'totalSteps' => $totalSteps,
                            'isEdit' => $isEdit,
                            'model' => $model,
                        ],
                        compact('rule', 'attributeName')
                    ),
                    $buttons,
                    true
                )
                ->build();
        }

        $isAttributeRequired = $config['isRequired'] ?? true;
        $items = $this->getIntermediateField($modelName, $attributeName, []);
        $itemButtons = PaginationButtons::buildFromArray(
            $items,
            function (int $page) use ($attributeName) {
                return self::createRoute(
                    'a-a',
                    [
                        'a' => $attributeName,
                        'p' => $page,
                    ]
                );
            },
            function ($key, $item) use ($rule, $relation, $editableFieldId, $isAttributeRequired, $items) {
                try {
                    $model = $this->getModelByRelation(
                        $rule,
                        $relation,
                        $item[array_key_first($item)],
                        $editableFieldId
                    );
                    $label = $this->getLabel($model);
                    if ($editableFieldId) {
                        $id = $model->id;
                    } else {
                        $id = 'v_' . $key;
                    }
                } catch (Exception $ex) {
                    if (is_array($item)) {
                        return [];
                    }
                    $label = $item;
                    $id = 'v_' . $key;
                }
                $buttonParams = $this->prepareButton($relation, [
                    'text' => $label,
                    'callback_data' => self::createRoute(
                        'e-r-a',
                        [
                            'i' => $id,
                        ]
                    ),
                ]);

                return array_merge(
                    [$buttonParams],
                    (count($items) == 1 && $isAttributeRequired)
                        ? []
                        : [
                        [
                            'text' => Emoji::DELETE,
                            'callback_data' => self::createRoute(
                                'r-a',
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
        $isEmpty = empty($items);
        $buttons = $this->prepareButtons(
            $itemButtons,
            $this->generateSystemButtons(
                $modelName,
                $attributeName,
                compact('isEmpty', 'enableBackRoute')
            ),
            compact('isEmpty', 'config', self::FIELD_NAME_ATTRIBUTE, 'modelName')
        );
        $model = $this->getFilledModel($rule);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->renderAttribute(
                    $this->prepareViewFileName($rule, $attributeName),
                    [
                        'step' => $step,
                        'error' => $error,
                        'totalSteps' => $totalSteps,
                        'isEdit' => $isEdit,
                        'model' => $model,
                    ],
                    compact('rule', 'attributeName')
                ),
                $buttons
            )
            ->build();
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array $options
     *
     * @return array
     * @throws InvalidConfigException
     */
    private function generatePublicResponse(
        string $modelName,
        string $attributeName,
        array $options
    )
    {
        $enableBackRoute = ArrayHelper::getValue($options, 'enableBackRoute', false);
        $config = ArrayHelper::getValue($options, 'config', []);
        $rule = $this->getRule($modelName);
        $error = ArrayHelper::getValue($options, 'error', null);
        $state = $this->getState();
        $state->setName(
            self::createRoute(
                'en-a',
                [
                    'a' => $attributeName,
                ]
            )
        );
        $this->setIntermediateField($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $isEdit = !is_null($this->getIntermediateField($modelName, self::FIELD_NAME_ID, null));
        $attributeValue = $this->getIntermediateField($modelName, $attributeName, null);
        $isEmpty = empty($attributeValue);
        $systemButtons = $this->generateSystemButtons(
            $modelName,
            $attributeName,
            compact('isEmpty', 'enableBackRoute')
        );
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $buttons = $this->prepareButtons(
            [],
            $systemButtons,
            compact('config', 'isEmpty', 'modelName', self::FIELD_NAME_ATTRIBUTE)
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->renderAttribute(
                    $this->prepareViewFileName($rule, $attributeName),
                    [
                        'error' => $error,
                        'step' => $step,
                        'totalSteps' => $totalSteps,
                        'isEdit' => $isEdit,
                        'model' => $this->getFilledModel($rule),
                    ],
                    compact('rule', 'attributeName')
                ),
                $buttons,
                true
            )
            ->build();
    }

    /**
     * @param string $modelName
     * @param string $attributeName
     * @param array $options
     *
     * @return array
     */
    private function generateResponse(string $modelName, string $attributeName, array $options)
    {
        $rule = ArrayHelper::getValue($options, 'rule', []);
        $enableBackRoute = ArrayHelper::getValue($options, 'enableBackRoute', false);
        $config = $rule['attributes'][$attributeName];
        if ($this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
            $attributes = $config['relation']['attributes'];
            $this->setIntermediateField(
                $modelName,
                self::FIELD_NAME_RELATION,
                (count($attributes) == 1) ? array_keys($attributes)[0] : null
            );

            return $this->generatePrivateResponse(
                $modelName,
                $attributeName,
                compact('config', 'enableBackRoute')
            );
        } else {
            return $this->generatePublicResponse(
                $modelName,
                $attributeName,
                compact('config', 'enableBackRoute')
            );
        }
    }

    /**
     * If you call directly - you should use remove array keys
     *
     * @param bool $enableBackRoute
     *
     * @return array ['back => ['text' => 'this is text', 'callback_data' => 'route']]
     */
    private function getDefaultSystemButtons($enableBackRoute = false)
    {
        if ($enableBackRoute) {
            $backRoute = $this->endRoute->get();
        } else {
            $backRoute = self::createRoute('p-a');
        }
        $systemButtons = [];
        if ($backRoute) {
            /* 'Back' button */
            $systemButtons['back'] = [
                'text' => Emoji::BACK,
                'callback_data' => $backRoute,
            ];
        }
        $endButtonRoute = $this->endRoute->get();
        if ($endButtonRoute) {
            /* 'End' button */
            $systemButtons['end'] = [
                'callback_data' => $endButtonRoute,
                'text' => Emoji::END,
            ];
        }

        return $systemButtons;
    }

    /**
     *  System Buttons
     *  Possible to replace in controller rules
     *
     * 'model_property' => [
     *      'systemButtons' => [
     *          'back' => [
     *              'item' => 'description',
     *          ],
     *          'menu' => [
     *              'text' => Emoji::MENU
     *              'route' => MenuController::createRoute(),
     *          ],
     *          'end' => [
     *              'text' => 'Some text',
     *          ],
     *      ],
     * ]
     *
     * @param string $modelName
     * @param string $attributeName
     * @param array $options
     *
     * @return array
     */
    private function generateSystemButtons(string $modelName, string $attributeName, array $options)
    {
        $isEmpty = ArrayHelper::getValue($options, 'isEmpty', false);
        $enableBackRoute = ArrayHelper::getValue($options, 'enableBackRoute', false);
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $isFirstScreen = !strcmp($attributeName, array_key_first($attributes));
        if ($isFirstScreen) {
            $enableBackRoute = true;
        }
        $systemButtons = $this->getDefaultSystemButtons($enableBackRoute);
        $configSystemButtons = $this->attributeButtons->getSystems($rule, $attributeName);

        $isAttributeRequired = $config['isRequired'] ?? true;
        $relation = $this->getRelation($config);

        if (($config['enableDeleteButton'] ?? false) && (!isset($relation) || count($relation['attributes']) == 1)) {
            /* 'Clear' button */
            if (!$isAttributeRequired && !$isEmpty) {
                $systemButtons['delete'] = [
                    'text' => Emoji::DELETE,
                    'callback_data' => self::createRoute('c-a'),
                ];
            }
        } elseif ($config['enableAddButton'] ?? false) {
            /* 'Add' button */
            $systemButtons['add'] = [
                'text' => Emoji::ADD,
                'callback_data' => self::createRoute(
                    'a-a',
                    [
                        'a' => $attributeName,
                    ]
                ),
            ];
        }
        $systemButtons = ArrayHelper::merge($systemButtons, $configSystemButtons);
        if ($isFirstScreen) {
            unset($systemButtons['end']);
        }

        return array_values($systemButtons);
    }

    private function getStepsInfo(string $attributeName, array $rule)
    {
        $attributes = $this->getAttributes($rule);
        $totalSteps = count($attributes);
        $step = array_search($attributeName, array_keys($attributes)) + 1;

        return [$step, $totalSteps];
    }

    /**
     * @param array $rule
     *
     * @return string
     */
    public function getModelClassByRule($rule)
    {
        return $rule['model'];
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
            $object = Yii::createObject(['class' => $this->getModelClassByRule($rule)]);
            if ($object instanceof ActiveRecord) {
                return $object;
            }
            Yii::warning(
                'The \'model\' key must contain a name of the class that is inherited from ' . ActiveRecord::class
            );

            return null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @param array $rule
     * @param int $id
     *
     * @return ActiveRecord|null
     */
    private function getModel(array $rule, int $id)
    {
        $modelName = $this->getModelName($this->getModelClassByRule($rule));
        $getModelMethodName = 'get' . ucfirst($modelName);
        if (method_exists($this, $getModelMethodName)) {
            /* @var ActiveRecord $model */
            $model = call_user_func([$this, $getModelMethodName], $id);
        } else {
            $model = call_user_func([$this->getModelClassByRule($rule), 'findOne'], $id);
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
        $getKeyboardMethodName = "get" . ucfirst($modelName) . "Keyboard";
        if (method_exists($this, $getKeyboardMethodName)) {
            $keyboard = call_user_func([$this, $getKeyboardMethodName], $model);
        }

        return $keyboard ?? [];
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
            if ($this->getModelName($this->getModelClassByRule($rule)) == $modelName) {
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
            foreach ($attributes as $config) {
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
                if (!($model instanceof DynamicModel)) {
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
                                Yii::warning("$modelClassName doesn't have $value attribute");

                                return null;
                            }
                        }
                    } elseif (!$model->hasAttribute($refColumn)) {
                        Yii::warning("$modelClassName doesn't have $refColumn attribute");

                        return null;
                    }
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
        $modelName = $this->getModelName($this->getModelClassByRule($rule));
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

    /**
     * @param array $rule
     * @param string $attributeName
     * @param array $options
     *
     * @return string
     */
    private function prepareViewFileName(array $rule, string $attributeName, $options = [])
    {
        $relationAttributeName = ArrayHelper::getValue($options, 'relationAttributeName', null);

        if ($view = $rule['attributes'][$attributeName]['view'] ?? null) {
            return $view;
        }
        if ($relationAttributeName && ($rule['attributes'][$attributeName]['enableAddButton'] ?? false)) {
            $pathArray = [
                $attributeName,
                '/edit-',
                $relationAttributeName,
            ];
        } else {
            $pathArray = [
                'edit-',
                $attributeName,
            ];
        }

        return $this->viewFile->search(implode('', $pathArray));
    }

    /**
     * Search 'buttonFunction' attribute inside config array
     * and run function
     *
     * @param array $config
     * @param array $buttonParams
     *
     * @return array
     */
    private function prepareButton(array $config, array $buttonParams)
    {
        if ($buttonFunction = ($config['buttonFunction'] ?? null)) {
            $buttonParams = call_user_func($buttonFunction, $buttonParams);
        }

        return $buttonParams;
    }

    /** @inheritDoc */
    public function getState()
    {
        return parent::getState();
    }

    /** @inheritDoc */
    public function getTelegramUser()
    {
        return parent::getTelegramUser();
    }
}
