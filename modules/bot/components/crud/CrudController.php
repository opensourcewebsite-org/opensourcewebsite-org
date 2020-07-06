<?php

namespace app\modules\bot\components\crud;

use app\components\helpers\ArrayHelper;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\services\IntermediateFieldService;
use app\modules\bot\components\crud\services\ModelRelationService;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\request\Request;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\crud\rules\FieldInterface;
use app\modules\bot\components\crud\services\AttributeButtonsService;
use app\modules\bot\components\crud\services\BackRouteService;
use app\modules\bot\components\crud\services\EndRouteService;
use app\modules\bot\components\crud\services\ViewFileService;
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
    const FIELD_EDITING_ATTRIBUTES = 'editingAttributes';
    const FIELD_NAME_ID = 'id';
    const VALUE_NO = 'NO';

    /** @var BackRouteService */
    public $backRoute;
    /** @var EndRouteService */
    public $endRoute;
    /** @var AttributeButtonsService */
    public $attributeButtons;
    /** @var ViewFileService */
    public $viewFile;
    /** @var ModelRelationService */
    public $modelRelation;
    /** @var IntermediateFieldService */
    public $field;
    /** @var array */
    public $rule;
    /** @var array */
    private $manyToManyRelationAttributes;

    /** @inheritDoc */
    public function __construct($id, $module, $config = [])
    {
        $this->backRoute = Yii::createObject(
            [
                'class' => BackRouteService::class,
                'state' => $module->userState,
                'controller' => $this,
            ]
        );
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
        $this->viewFile = Yii::createObject(['class' => ViewFileService::class, 'controller' => $this,]);
        $this->modelRelation = Yii::createObject([
            'class' => ModelRelationService::class, 'controller' => $this,
        ]);
        $this->field = Yii::createObject([
            'class' => IntermediateFieldService::class, 'controller' => $this, 'state' => $module->userState,
        ]);
        parent::__construct($id, $module, $config);
    }

    /** @inheritDoc */
    public function bindActionParams($action, $params)
    {
        if (!method_exists(self::class, $action->actionMethod)) {
            $this->backRoute->make($action->id, $params);
            $this->endRoute->make($action->id, $params);
            foreach ($this->rules() as $rule) {
                $this->field->set($this->getModelName($rule['model']), self::FIELD_NAME_ID, null);
            }
        } elseif (!strcmp($action->actionMethod, 'actionU')) {
            $this->backRoute->make($action->id, $params);
            $this->field->reset();
        }
        if (isset($params['m'])) {
            $this->rule = $this->getRule($params['m']);
        } elseif ($modelClass = $this->getCurrentModelClass()) {
            $this->rule = $this->getRule($this->getModelName($modelClass));
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
        $this->field->reset();

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
     * @param string $modelName
     *
     * @return array
     */
    public function getEditingAttributes($modelName)
    {
        return $this->field->get($modelName, self::FIELD_EDITING_ATTRIBUTES, []);
    }

    /**
     * @param $modelName
     * @param $attributeName
     */
    public function addEditingAttribute($modelName, $attributeName)
    {
        $attributes = $this->getEditingAttributes($modelName);
        $attributes[$attributeName] = [];
        $this->field->set($modelName, self::FIELD_EDITING_ATTRIBUTES, $attributes);
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
        if ($text == self::VALUE_NO) {
            $text = null;
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
            $this->field->set($modelName, $fieldResult);
        } else {
            $this->field->set($modelName, $attributeName, $fieldResult);
        }

        $isEdit = !is_null($this->field->get($modelName, self::FIELD_NAME_ID, null));
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
     * @param null $i Attribute id
     * @param null $v Attribute value
     * @param null $text User Message
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionSA($a, $p = 1, $i = null, $v = null, $text = null)
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
        if ($text == self::VALUE_NO) {
            $shouldRemove = true;
            $text = null;
        } else {
            $shouldRemove = false;
        }
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $relation = $this->modelRelation->getRelation($config);
        $relationAttributes = $relation['attributes'];
        [
            $primaryRelation, $secondaryRelation, $thirdRelation,
        ] = $this->modelRelation->getRelationAttributes($relation);
        $isValidRequest = false;
        $component = $this->createAttributeComponent($config);
        if ($component instanceof FieldInterface) {
            $text = $component->prepare($text);
        }
        $editableRelationId = null;
        $error = null;
        if (isset($relation) && (isset($v) || isset($text))) {
            $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
            if (!$relationAttributeName && $secondaryRelation) {
                $isValidRequest = true;
                $relationData = $this->field->get($modelName, $attributeName, [[]]);
                if (!$text && !$v) {
                    $relationData = [
                        [$secondaryRelation[0] => null],
                    ];
                } elseif (is_array($text)) {
                    $relationData = $text;
                } else {
                    $relationData[] = $text;
                }
                $relationData = $this->modelRelation->prepareRelationData($attributeName, $relationData);
                $this->field->set($modelName, $attributeName, $relationData);
            } else {
                if (!array_key_exists($relationAttributeName, $relationAttributes)) {
                    return ResponseBuilder::fromUpdate($this->getUpdate())
                        ->answerCallbackQuery()
                        ->build();
                }
                $relationAttribute = $relationAttributes[$relationAttributeName];
                if ($v) {
                    if (in_array($relationAttributeName, $thirdRelation)) {
                        $relationModel = $this->modelRelation->getThirdModel($config, $v);
                    } elseif (in_array($relationAttributeName, $secondaryRelation)) {
                        $relationModel = $this->modelRelation->getSecondModel($config, $v);
                    } elseif (in_array($relationAttributeName, $primaryRelation)) {
                        $relationModel = $this->modelRelation->getFirstModel($config, $v);
                    }
                } elseif ($text && ($field = ($relationAttribute[2] ?? null))) {
                    $relationQuery = call_user_func([$relationAttribute[0], 'find']);
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
                $relationData = $this->field->get($modelName, $attributeName, [[]]);
                if (preg_match('|v_(\d+)|', $i, $match)) {
                    $id = $match[1];
                } elseif ($i) {
                    $model = $this->getRuleModel($relation, $i);
                    foreach ($relationData as $key => $relationItem) {
                        if (!is_array($relationItem)) {
                            continue;
                        }
                        if ($relationItem[$primaryRelation[0]] == $model->{$primaryRelation[0]}
                            && $relationItem[$secondaryRelation[0]] == $model->{$secondaryRelation[0]}) {
                            $id = $key;
                            break;
                        }
                    }
                }
                if (isset($id) && isset($relationData[$id])) {
                    $item = $relationData[$id];
                    unset($relationData[$id]);
                } else {
                    $item = array_pop($relationData);
                }
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
                $relationData = array_values($relationData);
                $editableRelationId = 'v_' . array_key_last($relationData);
                if ($isManyToOne && ($modelField = array_key_first($relationAttributes))) {
                    $this->field->set($modelName, $modelField, $relationModel->id);
                }
                $this->field->set($modelName, $attributeName, $relationData);

                $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                $this->field->set($modelName, self::FIELD_NAME_RELATION, $nextRelationAttributeName);

                if (!isset($nextRelationAttributeName)) {
                    $isValidRequest = true;
                }
            } else {
                $error = "not found";
            }
        }
        if ($shouldRemove) {
            $this->field->set($modelName, $attributeName, []);
            $isValidRequest = true;
        }
        if ($isValidRequest) {
            $isEdit = !is_null($this->field->get($modelName, self::FIELD_NAME_ID, null));

            if ($config['samePageAfterAdd'] ?? false) {
                $nextAttribute = $attributeName;
            } else {
                $nextAttribute = $this->getNextKey($attributes, $attributeName);
            }
            if (isset($nextAttribute) && !$isEdit) {
                return $this->generateResponse($modelName, $nextAttribute, compact('rule'));
            }
            $editingAttributes = $this->getEditingAttributes($modelName);
            $prevAttribute = $this->getPrevKey($editingAttributes, $attributeName);
            if ($prevAttribute) {
                $model = $this->getFilledModel($rule);
                $model->save();

                return $this->generateResponse($modelName, $prevAttribute, compact('rule'));
            }

            return $this->save($rule);
        }

        return $this->generatePrivateResponse(
            $modelName,
            $attributeName,
            ['config' => $config, 'page' => $p, 'error' => $error, 'editableRelationId' => $editableRelationId]
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
            $relation = $this->modelRelation->getRelation($config);
            [, $secondaryRelation] = $this->modelRelation->getRelationAttributes($relation);
            $this->field->set($modelName, self::FIELD_NAME_RELATION, $secondaryRelation[0]);
            $attributeValue = $this->field->get($modelName, $attributeName, [[]]);
            $attributeLastItem = end($attributeValue);
            if (!empty($attributeLastItem)) {
                $attributeValue[] = [];
            }
            $this->field->set($modelName, $attributeName, $attributeValue);
        } else {
            $this->field->set($modelName, self::FIELD_NAME_RELATION, null);
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
     *
     * @return array
     */
    public function actionEA($m, $a, $i)
    {
        $id = $i;
        $enableGlobalBackRoute = true;
        $attributeName = $a;
        $rule = $this->getRule($m);
        $attributes = $this->getAttributes($rule);
        if (!empty($attributes) && array_key_exists($attributeName, $attributes) && isset($id)) {
            $this->beforeEdit($rule, $attributeName, $id);

            return $this->generateResponse($m, $attributeName, compact('rule', 'enableGlobalBackRoute'));
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
        $id = $this->field->get($modelName, self::FIELD_NAME_ID);
        $model = $this->getFilledModel($rule);
        /** @var ActiveRecord $model */
        $model = call_user_func($config['buttons'][$i]['callback'], $model);
        if (!$model) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }
        $this->field->set($modelName, $model->getAttributes());

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
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            $config = $attributes[$attributeName];

            $isAttributeRequired = $config['isRequired'] ?? true;
            if (!$isAttributeRequired) {
                $this->field->set($modelName, $attributeName, null);

                $isEdit = !is_null($this->field->get($modelName, self::FIELD_NAME_ID, null));
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
        $isEdit = !is_null($this->field->get($modelName, self::FIELD_NAME_ID, null));
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        if (($relation = $this->modelRelation->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
            $relationAttributes = $relation['attributes'];
            array_shift($relationAttributes);
            $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
            if (isset($relationAttributeName)) {
                $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                $this->field->set($modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);

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
                $this->field->get($modelName, self::FIELD_NAME_ID, null)
            );
            $this->field->reset();

            return $response;
        }
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
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->field->get($modelName, self::FIELD_NAME_ID, null));
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->modelRelation->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
                if (isset($relationAttributeName)) {
                    $relationData = $this->field->get($modelName, $attributeName, [[]]);
                    $item = array_pop($relationData);
                    if (!empty($item[$relationAttributeName] ?? null)) {
                        $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                        $this->field->set(
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
            if (!$isAttributeRequired || !empty($this->field->get($modelName, $attributeName, null))) {
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
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $modelId = $this->field->get($modelName, self::FIELD_NAME_ID, null);
            $isEdit = !is_null($modelId);
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            $config = $attributes[$attributeName];
            $thirdRelation = [];
            if (($relation = $this->modelRelation->getRelation($config)) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
                [, , $thirdRelation] = $this->modelRelation->getRelationAttributes($relation);
                if (isset($relationAttributeName) && !in_array($relationAttributeName, $thirdRelation)) {
                    $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                    $this->field->set($modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);
                    if (!($config['createRelationIfEmpty'] ?? false) || $this->modelRelation->filledRelationCount($attributeName)) {
                        return $this->generatePrivateResponse(
                            $modelName,
                            $attributeName,
                            ['config' => $attributes[$attributeName]]
                        );
                    } else {
                        $relationAttributeName = null;
                    }
                }
            }
            if ($thirdRelation && $relationAttributeName) {
                $prevAttributeName = $attributeName;
            } else {
                $prevAttributeName = $this->getPrevKey($attributes, $attributeName);
            }
            if (isset($prevAttributeName) && !$isEdit) {
                return $this->generateResponse($modelName, $prevAttributeName, compact('rule'));
            } else {
                $response = $this->onCancel(
                    $this->getModelClassByRule($rule),
                    $this->field->get($modelName, self::FIELD_NAME_ID, null)
                );
                $this->field->reset();

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
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->modelRelation->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                [, $secondaryRelation] = $this->modelRelation->getRelationAttributes($relation);
                $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);

                $items = $this->field->get($modelName, $attributeName, []);
                if (preg_match('|v_(\d+)|', $i, $match)) {
                    unset($items[$match[1]]);
                } else {
                    $model = $this->getRuleModel($relation, $i);
                    if ($model) {
                        $model->delete();
                    }
                }
                $items = array_values($items);
                $this->field->set($modelName, $attributeName, $items);

                return $this->generateResponse(
                    $modelName,
                    $attributeName,
                    ['config' => $attributes[$attributeName], 'rule' => $this->rule]
                );
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
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $rule = $this->getRule($modelName);
            $attributes = $this->getAttributes($rule);
            if (($relation = $this->modelRelation->getRelation($attributes[$attributeName])) && count($relation['attributes']) > 1) {
                [
                    $primaryRelation, $secondaryRelation, $thirdRelation,
                ] = $this->modelRelation->getRelationAttributes($relation);
                $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
                if (!isset($relationAttributeName)) {
                    $this->field->set($modelName, self::FIELD_NAME_RELATION, $primaryRelation[0]);
                    $items = $this->field->get($modelName, $attributeName, []);
                    foreach ($items as $key => $item) {
                        if (!$item) {
                            unset($items[$key]);
                            continue;
                        }
                        if ($item[$secondaryRelation[0]] == $i) {
                            unset($items[$key]);
                            $items[] = $item;
                            break;
                        }
                    }
                    $this->field->set($modelName, $attributeName, $items);
                    if ($thirdRelation) {
                        $this->field->set($modelName, self::FIELD_NAME_RELATION, $thirdRelation[0]);
                    }

                    return $this->generatePrivateResponse(
                        $modelName,
                        $attributeName,
                        [
                            'config' => $attributes[$attributeName],
                            'editableRelationId' => $i,
                        ]
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
    public function actionU($m, $i)
    {
        $id = $i;
        $rule = $this->getRule($m);
        $attributes = array_keys($rule['attributes']);

        /* @var ActiveRecord $model */
        $model = $this->getRuleModel($rule, $id);
        $editButtons = array_map(
            function (string $attribute) use ($id, $m, $model, $rule) {
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
        $systemButtons = $this->getDefaultSystemButtons(true);
        if ($endRoute = $this->endRoute->get()) {
            $systemButtons = ArrayHelper::merge(
                $systemButtons,
                ['back' => ['callback_data' => $endRoute]]
            );
        }

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
        $model = $this->getRuleModel($rule, $id);
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
        $model = $this->getRuleModel($rule, $id);
        if (isset($model)) {
            $this->setCurrentModelClass($this->getModelClassByRule($rule));
            $modelName = $this->getModelName($this->getModelClassByRule($rule));
            $this->addEditingAttribute($modelName, $attributeName);
            $this->field->set($modelName, self::FIELD_NAME_ID, $id);

            if ($this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
                $relation = $this->modelRelation->getRelation($this->getAttributes($rule)[$attributeName]);
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
                $this->field->set($modelName, $attributeName, $value);
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
    public function getModelName(string $className)
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
     * @param array $manyToManyRelationAttributes
     *
     * @param array $options ['config' => [values of attribute]]
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    private function fillModel($model, $attributeName, &$manyToManyRelationAttributes, $options)
    {
        $attributeConfig = ArrayHelper::getValue($options, 'config', []);
        $editingAttributes = ArrayHelper::getValue($options, 'editingAttributes', []);
        $ignoreEditingAttributes = ArrayHelper::getValue($options, 'ignoreEditingAttributes', false);
        $state = $this->getState();
        $modelName = $this->getModelName($model::className());
        if (!$ignoreEditingAttributes && !$editingAttributes) {
            $editingAttributes = $this->getEditingAttributes($modelName);
            unset($editingAttributes[$attributeName]);
            $rule = $this->getRule($modelName);
            foreach ($editingAttributes as $field => $config) {
                $config = array_merge($config, $rule['attributes'][$field]);
                $this->fillModel(
                    $model,
                    $field,
                    $manyToManyRelationAttributes,
                    compact('config', 'editingAttributes')
                );
            }
        }
        $component = $this->createAttributeComponent($attributeConfig);
        if ($component instanceof FieldInterface) {
            $fields = $component->getFields();
            foreach ($fields as $field) {
                $this->fillModel($model, $field, $manyToManyRelationAttributes, [
                    'config' => $attributeConfig['component'],
                    'ignoreEditingAttributes' => true,
                ]);
            }
        }
        if ($state->isIntermediateFieldExists($this->field->createName($modelName, $attributeName))) {
            $relation = $this->modelRelation->getRelation($attributeConfig);
            if (isset($relation)) {
                $relationAttributes = $relation['attributes'];
                if (count($relationAttributes) > 1) {
                    $manyToManyRelationAttributes[] = $attributeName;

                    return $model;
                }
                if (count($relation) == 1) {
                    $relationValue = $this->field->get($modelName, $attributeName, [[]]);
                    $model->setAttributes($relationValue[0]);
                }
            } else {
                $value = $this->field->get($modelName, $attributeName, null);
                $model->setAttribute($attributeName, $value ?? null);
            }
        }

        return $model;
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
        $id = $this->field->get($modelName, self::FIELD_NAME_ID, null);
        $attributeName = $this->field->get($modelName, self::FIELD_NAME_ATTRIBUTE, null);
        $isNew = is_null($id);
        $manyToManyRelationAttributes = [];
        /* @var ActiveRecord $model */
        if ($isNew) {
            $model = $this->createModel($rule);
            foreach ($rule['attributes'] as $attributeName => $config) {
                $this->fillModel($model, $attributeName, $manyToManyRelationAttributes, compact('config'));
            }
            $model->attachBehaviors($this->getRuleBehaviors($rule));
        } else {
            $model = $this->getRuleModel($rule, $id);
            if ($attributeName) {
                $this->fillModel(
                    $model,
                    $attributeName,
                    $manyToManyRelationAttributes,
                    ['config' => $rule['attributes'][$attributeName]]
                );
            }
        }
        $this->manyToManyRelationAttributes = $manyToManyRelationAttributes;

        return $model;
    }

    /**
     * Return model for main relation
     * protected function rules()
     * {
     *      return [
     *      [
     *          'model' => Model::class,
     *          'relation' => [
     *              'model' => RelationModel::class,
     *              'attributes' => [
     *                  'company_id' => [Model::class, 'id'],
     *                  'user_id' => [DynamicModel::class, 'id'],
     *              ],
     *              'component' => [
     *              'class' => CurrentUserFieldComponent::class,
     *          ],
     *      ],
     * }
     *
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
            ] = $this->modelRelation->getRelationAttributes($relation);
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
        $id = $this->field->get($modelName, self::FIELD_NAME_ID, null);
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
                        $relation = $this->modelRelation->getRelation($rule['attributes'][$attributeName]);
                        $relationModelClass = $relation['model'];

                        [
                            $primaryRelation, $secondaryRelation, $thirdRelation,
                        ] = $this->modelRelation->getRelationAttributes($relation);

                        $attributeValues = $this->field->get($modelName, $attributeName, []);
                        $appendedIds = [];
                        foreach ($attributeValues as $attributeValue) {
                            if (!$attributeValue) {
                                continue;
                            }
                            $useDynamicModel = false;
                            if (new $secondaryRelation[2] instanceof DynamicModel) {
                                $useDynamicModel = true;
                                if (!is_array($attributeValue)) {
                                    $text = $attributeValue;
                                    $attributeValue = [];
                                    $attributeValue[$secondaryRelation[0]] = $text;
                                }
                            } elseif (!is_array($attributeValue)) {
                                if (isset($secondaryRelation[3])
                                    && ($relationModel = $this->findOrCreateRelationModel(
                                        $secondaryRelation[2],
                                        $secondaryRelation[3],
                                        $attributeValue
                                    ))) {
                                    $attributeValue = [];
                                    $attributeValue[$secondaryRelation[0]] = $relationModel->id;
                                } else {
                                    continue;
                                }
                            }
                            $conditions = [
                                $primaryRelation[0] => $model->getAttribute(
                                    $primaryRelation[1]
                                ),
                            ];
                            if (!$useDynamicModel) {
                                $conditions[$secondaryRelation[0]] = $attributeValue[$secondaryRelation[0]];
                            }
                            /** @var ActiveRecord $relationModel */
                            $relationModel = call_user_func(
                                [$relationModelClass, 'findOne'],
                                $conditions
                            );
                            if ($relationModel) {
                                if (is_array($attributeValue) && !$attributeValue[$secondaryRelation[0]]) {
                                    $relationModel->delete();
                                    continue;
                                }
                            } else {
                                if (is_array($attributeValue) && !$attributeValue[$secondaryRelation[0]]) {
                                    continue;
                                }
                                $relationModel = Yii::createObject(
                                    [
                                        'class' => $relationModelClass,
                                    ]
                                );
                            }
                            $relationModel->setAttribute(
                                $primaryRelation[0],
                                $model->getAttribute($primaryRelation[1])
                            );
                            foreach ($attributeValue as $name => $value) {
                                $relationModel->setAttribute($name, $value);
                            }
                            try {
                                if (!$relationModel->save()) {
                                    throw new Exception("not possible to save "
                                        . $relationModel->formName() . " because " . serialize($relationModel->getErrors()));
                                }
                            } catch (\yii\db\Exception $exception) {
                                Yii::error("Row in " . $relationModelClass
                                    . " was not added with attributes " . serialize($attributeValue)
                                    . " because: " . $exception->getMessage());
                            }
                            $appendedIds[] = $relationModel->id;
                        }

                        if (!$isNew && ($relation['removeOldRows'] ?? null)) {
                            /* @var ActiveQuery $query */
                            $query = call_user_func([$relationModelClass, 'find'], []);
                            $itemsToDelete = $query->where([
                                'NOT IN',
                                $primaryRelation[1],
                                $appendedIds,
                            ])->andWhere([$primaryRelation[0] => $model->id])->all();
                            foreach ($itemsToDelete as $itemToDelete) {
                                $itemToDelete->delete();
                            }
                        }
                    }
                    $this->field->reset();
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
        $attributeName = ArrayHelper::getValue($options, self::FIELD_NAME_ATTRIBUTE, null);
        $config = ArrayHelper::getValue($options, 'config', []);

        $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
        $isAttributeRequired = $config['isRequired'] ?? true;
        $rule = $this->getRule($modelName);
        $modelId = $this->field->get($modelName, self::FIELD_NAME_ID, null);
        $isEdit = !is_null($modelId);
        if (!$relationAttributeName) {
            $configButtons = $this->attributeButtons->get($rule, $attributeName, $modelId);
        } else {
            $configButtons = [];
        }
        if ($configButtons) {
            foreach ($configButtons as $configButton) {
                $buttons[] = [$configButton];
            }
        }
        /* 'Next' button */
        if (!$relationAttributeName && !$isAttributeRequired) {
            $buttonSkip = $config['buttonSkip'] ?? [];
            $isPrivateAttribute = $this->attributeButtons->isPrivateAttribute($attributeName, $rule);
            $buttonSkip = ArrayHelper::merge(
                [
                    'text' => Yii::t('bot', $isEdit ? 'NO' : 'SKIP'),
                    'callback_data' => self::createRoute(
                        $isPrivateAttribute ? 's-a' : 'en-a',
                        ['a' => $attributeName, 'text' => self::VALUE_NO]
                    ),
                ],
                $buttonSkip
            );

            $buttons[] = [$buttonSkip];
        }

        return array_merge($buttons, [$systemButtons]);
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
        $enableGlobalBackRoute = ArrayHelper::getValue($options, 'enableGlobalBackRoute', false);
        $error = ArrayHelper::getValue($options, 'error', null);
        $editableRelationId = ArrayHelper::getValue($options, 'editableRelationId', null);
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
        $this->field->set($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
        $modelId = $this->field->get($modelName, self::FIELD_NAME_ID, null);

        $isEdit = !is_null($modelId);
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $relation = $this->modelRelation->getRelation($config);
        $attributeValues = $this->field->get($modelName, $attributeName, []);
        [
            $primaryRelation, $secondaryRelation, $thirdRelation,
        ] = $this->modelRelation->getRelationAttributes($relation);
        $relationModel = null;
        if ($editableRelationId && in_array($relationAttributeName, $thirdRelation)) {
            if (preg_match('|v_(\d+)|', $editableRelationId, $match)) {
                $id = $attributeValues[$match[1]][$secondaryRelation[0]] ?? null;
            } else {
                $model = $this->getRuleModel($relation, $editableRelationId);
                $id = $model->{$secondaryRelation[0]};
            }
            $relationModel = call_user_func([$secondaryRelation[2], 'findOne'], $id);
        }
        if (isset($relationAttributeName)
            && ($relationAttribute = $relation['attributes'][$relationAttributeName])) {
            if (!strcmp($this->getModelName($relationAttribute[0]), $modelName)) {
                $attributes = $this->getAttributes($rule);
                $nextAttribute = $this->getNextKey($attributes, $attributeName);

                if (isset($nextAttribute)) {
                    return $this->generateResponse($modelName, $nextAttribute, compact('rule', 'enableGlobalBackRoute'));
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
                    function ($key, ActiveRecord $model) use ($editableRelationId, $attributeName, $valueAttribute) {
                        return [
                            'text' => $this->getLabel($model),
                            'callback_data' => self::createRoute(
                                's-a',
                                [
                                    'a' => $attributeName,
                                    'v' => $model->getAttribute($valueAttribute),
                                    'i' => $editableRelationId,
                                ]
                            ),
                        ];
                    },
                    $page
                );
            }
            $isEmpty = empty($attributeValues);
            $systemButtons = $this->generateSystemButtons(
                $modelName,
                $attributeName,
                compact('isEmpty', 'enableGlobalBackRoute', 'modelId', 'editableRelationId')
            );
            $buttons = $this->prepareButtons(
                $itemButtons,
                $systemButtons,
                compact('config', 'isEmpty', 'modelName', 'attributeName')
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
                            'relationModel' => $relationModel,
                        ],
                        compact('rule', 'attributeName')
                    ),
                    $buttons,
                    true
                )
                ->build();
        }

        $isAttributeRequired = $config['isRequired'] ?? true;
        $itemButtons = PaginationButtons::buildFromArray(
            $attributeValues,
            function (int $page) use ($attributeName) {
                return self::createRoute(
                    'a-a',
                    [
                        'a' => $attributeName,
                        'p' => $page,
                    ]
                );
            },
            function ($key, $item) use ($rule, $relation, $modelId, $isAttributeRequired, $secondaryRelation, $attributeValues) {
                try {
                    if ($modelId) {
                        $model = $this->modelRelation->getMainModel(
                            $relation,
                            $modelId,
                            $item[$secondaryRelation[0]],
                        );
                    } else {
                        $model = $this->modelRelation->fillModel($relation['model'], $item);
                    }
                    if ($model) {
                        $label = $this->getLabel($model);
                        $id = $model->id;
                    } else {
                        $label = $item;
                    }
                    if (!$id) {
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
                    (is_array($item) && count($item) > 1) || (count($attributeValues) == 1 && $isAttributeRequired)
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
        if (!($config['showRowsList'] ?? false)) {
            $itemButtons = [];
        }
        $isEmpty = empty($items);
        $buttons = $this->prepareButtons(
            $itemButtons,
            $this->generateSystemButtons(
                $modelName,
                $attributeName,
                compact('isEmpty', 'enableGlobalBackRoute', 'modelId')
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
        $enableGlobalBackRoute = ArrayHelper::getValue($options, 'enableGlobalBackRoute', false);
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
        $this->field->set($modelName, self::FIELD_NAME_ATTRIBUTE, $attributeName);

        $modelId = $this->field->get($modelName, self::FIELD_NAME_ID, null);
        $isEdit = !is_null($modelId);
        $attributeValue = $this->field->get($modelName, $attributeName, null);
        $isEmpty = empty($attributeValue);
        $systemButtons = $this->generateSystemButtons(
            $modelName,
            $attributeName,
            compact('isEmpty', 'enableGlobalBackRoute', 'modelId')
        );
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $buttons = $this->prepareButtons(
            [],
            $systemButtons,
            compact('config', 'isEmpty', 'modelName', 'attributeName')
        );
        $model = $this->getFilledModel($rule);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->renderAttribute(
                    $this->prepareViewFileName($rule, $attributeName),
                    [
                        'error' => $error,
                        'step' => $step,
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
        $enableGlobalBackRoute = ArrayHelper::getValue($options, 'enableGlobalBackRoute', false);
        $config = $this->rule['attributes'][$attributeName];
        if ($this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
            $relationAttributes = $config['relation']['attributes'];
            $relationAttributeName = (count($relationAttributes) == 1) ? array_keys($relationAttributes)[0] : null;
            $this->field->set(
                $modelName,
                self::FIELD_NAME_RELATION,
                $relationAttributeName
            );

            $response = $this->generatePrivateResponse(
                $modelName,
                $attributeName,
                compact('config', 'enableGlobalBackRoute')
            );

            if (($config['createRelationIfEmpty'] ?? false) && !$this->modelRelation->filledRelationCount($attributeName)) {
                return $this->actionAA($attributeName);
            }

            return $response;
        } else {
            return $this->generatePublicResponse(
                $modelName,
                $attributeName,
                compact('config', 'enableGlobalBackRoute')
            );
        }
    }

    /**
     * If you call directly - you should use remove array keys
     *
     * @param bool $enableGlobalBackRoute
     * @param bool $enableEndRoute
     *
     * @return array ['back => ['text' => 'this is text', 'callback_data' => 'route']]
     */
    private function getDefaultSystemButtons($enableGlobalBackRoute = false, $enableEndRoute = false)
    {
        if ($enableGlobalBackRoute) {
            $backRoute = $this->backRoute->get();
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
        if ($enableEndRoute && ($endButtonRoute = $this->endRoute->get())) {
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
        $modelId = ArrayHelper::getValue($options, 'modelId', null);
        $editableRelationId = ArrayHelper::getValue($options, 'editableRelationId', null);
        $isEdit = !is_null($modelId);
        $enableGlobalBackRoute = ArrayHelper::getValue($options, 'enableGlobalBackRoute', false);
        $rule = $this->getRule($modelName);
        $attributes = $this->getAttributes($rule);
        $config = $attributes[$attributeName];
        $isFirstScreen = !strcmp($attributeName, array_key_first($attributes));
        if ($isFirstScreen || $isEdit) {
            $enableGlobalBackRoute = true;
        }
        $systemButtons = $this->getDefaultSystemButtons($enableGlobalBackRoute, !$isEdit);
        $configSystemButtons = $this->attributeButtons->getSystems($rule, $attributeName, $modelId);
        $editingAttributes = $this->getEditingAttributes($modelName);
        if ($editingAttributes && ($prevAttribute = $this->getPrevKey($editingAttributes, $attributeName))) {
            $systemButtons['back']['callback_data'] = $this->attributeButtons->createAttributeRoute($modelName, $prevAttribute, $modelId);
        }

        $relationAttributeName = $this->field->get($modelName, self::FIELD_NAME_RELATION, null);
        $isAttributeRequired = $config['isRequired'] ?? true;
        $relation = $this->modelRelation->getRelation($config);
        [, $secondRelation, $thirdRelation] = $this->modelRelation->getRelationAttributes($relation);
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
        if ($relationAttributeName && in_array($relationAttributeName, $thirdRelation)) {
            $systemButtons['delete'] = [
                'text' => Emoji::DELETE,
                'callback_data' => self::createRoute(
                    'r-a',
                    [
                        'i' => $editableRelationId,
                    ]
                ),
            ];
        }
        $systemButtons = ArrayHelper::merge($systemButtons, $configSystemButtons);
        if ($isFirstScreen) {
            unset($systemButtons['end']);
        }
        if ($relationAttributeName) {
            unset($systemButtons['add']);
        }
        if (($config['createRelationIfEmpty'] ?? false) && $this->modelRelation->filledRelationCount($attributeName) <= 1) {
            unset($systemButtons['delete']);
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
    public function getRuleModel(array $rule, int $id)
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
    public function getAttributes(array $rule)
    {
        if (is_array($rule) && array_key_exists('attributes', $rule) && is_array($rule['attributes'])) {
            return $rule['attributes'];
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
        $isEmptyAttribute = empty($this->field->get($modelName, $attributeName, null));

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
