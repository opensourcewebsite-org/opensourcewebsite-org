<?php

namespace app\modules\bot\components\crud;

use Yii;
use app\modules\bot\components\Controller;
use app\components\helpers\ArrayHelper;
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
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;
use app\modules\bot\controllers\privates\MenuController;

/**
 * Class CrudController
 *
 * @package app\modules\bot\components
 */
abstract class CrudController extends Controller
{
    public const FIELD_NAME_RELATION = 'relationAttributeName';
    public const FIELD_NAME_MODEL_CLASS = 'modelClass';
    public const FIELD_NAME_ATTRIBUTE = 'attributeName';
    public const FIELD_EDITING_ATTRIBUTES = 'editingAttributes';
    public const FIELD_NAME_ID = 'id';
    public const VALUE_NO = 'NO';

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
    public $attributes;
    /** @var object */
    public $model;
    /** @var array */
    private $manyToManyRelationAttributes;
    /** @var array */
    protected $updateAttributes;
    /** @var boolean */
    protected $enableGlobalBackRoute = false;
    /** @var boolean */
    protected $enableEndRoute = true;
    
    /** @inheritDoc */
    public function __construct($id, $module, $config = [])
    {
        $this->backRoute = Yii::createObject([
            'class' => BackRouteService::class,
            'state' => $module->getUserState(),
            'controller' => $this,
        ]);
        $this->endRoute = Yii::createObject([
            'class' => EndRouteService::class,
            'state' => $module->getUserState(),
            'controller' => $this,
        ]);
        $this->attributeButtons = Yii::createObject([
            'class' => AttributeButtonsService::class,
            'controller' => $this,
        ]);
        $this->viewFile = Yii::createObject([
            'class' => ViewFileService::class,
            'controller' => $this,
        ]);
        $this->modelRelation = Yii::createObject([
            'class' => ModelRelationService::class,
            'controller' => $this,
        ]);
        $this->field = Yii::createObject([
            'class' => IntermediateFieldService::class,
            'state' => $module->getUserState(),
            'controller' => $this,
        ]);

        parent::__construct($id, $module, $config);
    }

    public function init()
    {
        $this->layout = 'main';
        parent::init();
    }

    /** @inheritDoc */
    public function bindActionParams($action, $params)
    {
        if (!method_exists(self::class, $action->actionMethod)) {
            $this->backRoute->make($action->id, $params);
            $this->endRoute->make($action->id, $params);
            $this->field->set($this->modelName, self::FIELD_NAME_ID, null);
        } elseif (!strcmp($action->actionMethod, 'actionUpdate')) {
            $this->backRoute->make($action->id, $params);
            $this->field->reset();
        }

        $this->rule = $this->rules() ?? [];
        $this->attributes = $this->rule['attributes'] ?? [];

        return parent::bindActionParams($action, $params);
    }

    /**
     * @return array
     */
    public function actionCreate()
    {
        $this->field->reset();
        $attribute = array_keys($this->attributes)[0];

        return $this->generateResponse($this->modelName, $attribute, [
            'rule' => $this->rule,
        ]);
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
     * @return array
     */
    public function getEditingAttributes()
    {
        return $this->field->get($this->modelName, self::FIELD_EDITING_ATTRIBUTES, []);
    }

    /**
     * @param string $attributeName
     */
    public function addEditingAttribute(string $attributeName)
    {
        $attributes = $this->getEditingAttributes($this->modelName);
        $attributes[$attributeName] = [];

        return $this->field->set($this->modelName, self::FIELD_EDITING_ATTRIBUTES, $attributes);
    }

    /**
     * Enter Attribute
     *
     * @param string $a Attribute name
     * @param null $text
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionEnA(string $a, $text = null)
    {
        $attributeName = $a;
        if (!$this->isRequestValid($attributeName)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        if ($text == self::VALUE_NO) {
            $text = null;
        }

        if ($this->attributeButtons->isPrivateAttribute($attributeName, $this->rule)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        /* @var ActiveRecord $model */
        $model = $this->createModel($this->rule);
        $config = $this->getAttributeRule($attributeName);
        $fieldResult = $text;
        $component = $this->createAttributeComponent($config);

        if ($component instanceof FieldInterface) {
            $fieldResult = $component->prepare($text);
        }

        $errors = false;

        if (is_array($fieldResult)) {
            $model->setAttributes($fieldResult);
            if (!$model->validate($component->getFields())) {
                $errors = $model->getErrors();
            }
        } else {
            $model->setAttribute($attributeName, $fieldResult);
            if (!$model->validate($attributeName)) {
                $errors = $model->getErrors($attributeName);
            }
        }

        if ($errors) {
            Yii::$app->view->params['errors'] = $errors; // Pass errors into layout
            return $this->generatePublicResponse(
                $this->modelName,
                $attributeName,
                [
                    'config' => $config,
                    'error' => $errors,
                ]
            );
        }

        if (is_array($fieldResult)) {
            $this->field->set($this->modelName, $fieldResult);
        } else {
            $this->field->set($this->modelName, $attributeName, $fieldResult);
        }

        $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));
        if (isset($this->rule['isVirtual']) && !empty($this->getEditingAttributes())) {
            $isEdit = true;
        }

        $nextAttribute = $this->getNextKey($this->attributes, $attributeName);

        if (isset($nextAttribute) && !$isEdit) {
            return $this->generateResponse($this->modelName, $nextAttribute, [
                'rule' => $this->rule,
            ]);
        }

        return $this->save();
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
    public function actionSA(string $a, $p = 1, $i = null, $v = null, $text = null)
    {
        $attributeName = $a;

        if (!$this->isRequestValid($attributeName)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $rule = $this->getRule($this->modelName);

        if (!$this->attributeButtons->isPrivateAttribute($attributeName, $rule)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        if ($text == self::VALUE_NO) {
            $shouldRemove = true;
            $text = null;
        } else {
            $shouldRemove = false;
        }
        $config = $this->getAttributeRule($attributeName);
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
            $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);
            if (!$relationAttributeName && $secondaryRelation) {
                $isValidRequest = true;
                $relationData = $this->field->get($this->modelName, $attributeName, [[]]);
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
                $this->field->set($this->modelName, $attributeName, $relationData);
            } else {
                if (!array_key_exists($relationAttributeName, $relationAttributes)) {
                    return $this->getResponseBuilder()
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
                $relationData = $this->field->get($this->modelName, $attributeName, [[]]);
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
                    $this->field->set($this->modelName, $modelField, $relationModel->id);
                }
                $this->field->set($this->modelName, $attributeName, $relationData);

                $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $nextRelationAttributeName);

                if (!isset($nextRelationAttributeName)) {
                    $isValidRequest = true;
                }
            } else {
                $error = "not found";
            }
        }
        if ($shouldRemove) {
            $this->field->set($this->modelName, $attributeName, []);
            $isValidRequest = true;
        }
        if ($isValidRequest) {
            $editingAttributes = $this->getEditingAttributes();
            $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));

            if (isset($this->rule['isVirtual']) && !empty($editingAttributes)) {
                $isEdit = true;
            }

            if ($config['samePageAfterAdd'] ?? false) {
                $nextAttribute = $attributeName;
            } else {
                $nextAttribute = $this->getNextKey($this->attributes, $attributeName);
            }
            if (isset($nextAttribute) && !$isEdit) {
                return $this->generateResponse($this->modelName, $nextAttribute, compact('rule'));
            }
            $prevAttribute = $this->getPrevKey($editingAttributes, $attributeName);
            if ($prevAttribute && !isset($this->rule['isVirtual']) && !$isEdit) {
                $model = $this->getFilledModel($rule);
                $model->save();

                return $this->generateResponse($this->modelName, $prevAttribute, compact('rule'));
            }

            return $this->save();
        }

        return $this->generatePrivateResponse(
            $this->modelName,
            $attributeName,
            [
                'config' => $config,
                'page' => $p,
                'error' => $error,
                'editableRelationId' => $editableRelationId,
            ]
        );
    }

    /**
     * Add Attribute
     *
     * @param string $a Attribute name
     * @param null $p
     *
     * @return array
     */
    public function actionAA(string $a, $p = null)
    {
        $attributeName = $a;
        if (!$this->isRequestValid($attributeName)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }
        $rule = $this->getRule($this->modelName);
        $config = $rule['attributes'][$attributeName];
        if (!isset($p)) {
            $relation = $this->modelRelation->getRelation($config);
            [, $secondaryRelation] = $this->modelRelation->getRelationAttributes($relation);
            $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $secondaryRelation[0]);
            $attributeValue = $this->field->get($this->modelName, $attributeName, [[]]);
            $attributeLastItem = end($attributeValue);
            if (!empty($attributeLastItem)) {
                $attributeValue[] = [];
            }
            $this->field->set($this->modelName, $attributeName, $attributeValue);
        } else {
            $this->field->set($this->modelName, self::FIELD_NAME_RELATION, null);
        }

        return $this->generatePrivateResponse(
            $this->modelName,
            $attributeName,
            [
                'config' => $config,
                'page' => $p ?? 1,
            ]
        );
    }

    /**
     * Edit Attribute
     *
     * @param string $a Attribute name
     * @param int $id
     *
     * @return array
     */
    public function actionEA(string $a, int $id = null)
    {
        $this->enableGlobalBackRoute = true;
        $attributeName = &$a;

        if (array_key_exists($attributeName, $this->attributes)) {
            if (isset($this->rule['isVirtual'])) {
                $model = new $this->rule['model'];
            }
            else {
                $model = $this->getRuleModel($this->rule, $id);
            }
            if (isset($model)) {
                $this->addEditingAttribute($attributeName);
                $attributeRule = $this->attributes[$attributeName];
                $this->field->set($this->modelName, self::FIELD_NAME_ID, $id);

                if ($this->attributeButtons->isPrivateAttribute($attributeName, $this->rule)) {
                    $relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName));
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
                    $this->field->set($this->modelName, $attributeName, $value);
                }
            }

            return $this->generateResponse($this->modelName, $attributeName, [
                'rule' => $this->rule,
            ]);
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * Button Callback
     *
     * @param string $a Attribute name
     * @param int $i Button number
     * @param int $id Model id
     *
     * @return array
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionBC($a, $i = 0, $id = null)
    {
        $attributeName = &$a;

        if (!$this->isRequestValid($attributeName)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $attributeRule = $this->getAttributeRule($attributeName);
        $model = $this->getFilledModel($this->rule);

        /** @var ActiveRecord $model */
        $model = call_user_func($attributeRule['buttons'][$i]['callback'], $model);

        if (!$model) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->field->set($this->modelName, $model->getAttributes());

        if ($model->isNewRecord) {
            $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));
            if (isset($this->rule['isVirtual']) && !empty($this->getEditingAttributes())) {
                $isEdit = true;
            }

            $nextAttribute = $this->getNextKey($this->attributes, $attributeName);
            if (isset($nextAttribute) && !$isEdit) {
                return $this->generateResponse($this->modelName, $nextAttribute, [
                    'rule' => $this->rule,
                ]);
            }
        }

        return $this->save();
    }

    /**
     * Clear Attribute
     *
     * @return array
     */
    public function actionCA()
    {
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);

        if (isset($attributeName)) {
            $config = $this->getAttributeRule($attributeName);
            $isAttributeRequired = $config['isRequired'] ?? true;

            if (!$isAttributeRequired) {
                $this->field->set($this->modelName, $attributeName, null);

                $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));

                if ($isEdit) {
                    return $this->save();
                }

                return $this->generateResponse($this->modelName, $attributeName, [
                    'rule' => $this->rule,
                ]);
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * Show Attribute
     *
     * @param string $a Attribute name
     *
     * @return array
     */
    public function actionShA($a)
    {
        $attributeName = $a;
        $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));
        if (($relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName))) && count($relation['attributes']) > 1) {
            $relationAttributes = $relation['attributes'];
            array_shift($relationAttributes);
            $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);
            if (isset($relationAttributeName)) {
                $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);

                return $this->generatePrivateResponse(
                    $this->modelName,
                    $attributeName,
                    [
                        'config' => $this->getAttributeRule($attributeName),
                    ]
                );
            }
        }
        if (!$isEdit) {
            return $this->generateResponse($this->modelName, $attributeName, [
                'rule' => $this->rule,
            ]);
        } else {
            $response = $this->onCancel(
                $this->modelClass,
                $this->field->get($this->modelName, self::FIELD_NAME_ID, null)
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
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $isEdit = !is_null($this->field->get($this->modelName, self::FIELD_NAME_ID, null));
            if (($relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName))) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);
                if (isset($relationAttributeName)) {
                    $relationData = $this->field->get($this->modelName, $attributeName, [[]]);
                    $item = array_pop($relationData);
                    if (!empty($item[$relationAttributeName] ?? null)) {
                        $nextRelationAttributeName = $this->getNextKey($relationAttributes, $relationAttributeName);
                        $this->field->set(
                            $this->modelName,
                            self::FIELD_NAME_RELATION,
                            $nextRelationAttributeName
                        );

                        return $this->generatePrivateResponse(
                            $this->modelName,
                            $attributeName,
                            [
                                'config' => $this->getAttributeRule($attributeName),
                            ]
                        );
                    }

                    return $this->getResponseBuilder()
                        ->answerCallbackQuery()
                        ->build();
                }
            }
            $nextAttributeName = $this->getNextKey($this->attributes, $attributeName);
            $isAttributeRequired = $this->getAttributeRule($attributeName)['isRequired'] ?? true;
            if (!$isAttributeRequired || !empty($this->field->get($this->modelName, $attributeName, null))) {
                if (isset($nextAttributeName) && !$isEdit) {
                    return $this->generateResponse($this->modelName, $nextAttributeName, [
                        'rule' => $this->rule,
                    ]);
                } else {
                    return $this->save();
                }
            }
        }

        return $this->getResponseBuilder()
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
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            $modelId = $this->field->get($this->modelName, self::FIELD_NAME_ID, null);
            $isEdit = !is_null($modelId);
            $config = $this->getAttributeRule($attributeName);
            $thirdRelation = [];
            if (($relation = $this->modelRelation->getRelation($config)) && count($relation['attributes']) > 1) {
                $relationAttributes = $relation['attributes'];
                array_shift($relationAttributes);
                $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);
                [, , $thirdRelation] = $this->modelRelation->getRelationAttributes($relation);
                if (isset($relationAttributeName) && !in_array($relationAttributeName, $thirdRelation)) {
                    $prevRelationAttributeName = $this->getPrevKey($relationAttributes, $relationAttributeName);
                    $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $prevRelationAttributeName);
                    if (!($config['createRelationIfEmpty'] ?? false) || $this->modelRelation->filledRelationCount($attributeName)) {
                        return $this->generatePrivateResponse(
                            $this->modelName,
                            $attributeName,
                            [
                                'config' => $config,
                            ]
                        );
                    } else {
                        $relationAttributeName = null;
                    }
                }
            }
            if ($thirdRelation && $relationAttributeName) {
                $prevAttributeName = $attributeName;
            } else {
                $prevAttributeName = $this->getPrevKey($this->attributes, $attributeName);
            }
            if (isset($prevAttributeName) && !$isEdit) {
                $rule = $this->getRule($this->modelName);
                return $this->generateResponse($this->modelName, $prevAttributeName, compact('rule'));
            } else {
                $response = $this->onCancel(
                    $this->modelClass,
                    $this->field->get($this->modelName, self::FIELD_NAME_ID, null)
                );
                $this->field->reset();

                return $response;
            }
        }

        return $this->getResponseBuilder()
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
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);

        if (isset($attributeName)) {
            if (($relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName))) && count($relation['attributes']) > 1) {
                [, $secondaryRelation] = $this->modelRelation->getRelationAttributes($relation);
                $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);

                $items = $this->field->get($this->modelName, $attributeName, []);
                if (preg_match('|v_(\d+)|', $i, $match)) {
                    unset($items[$match[1]]);
                } else {
                    $model = $this->getRuleModel($relation, $i);
                    if ($model) {
                        $model->delete();
                    }
                }
                $items = array_values($items);
                $this->field->set($this->modelName, $attributeName, $items);

                return $this->generateResponse(
                    $this->modelName,
                    $attributeName,
                    [
                        'config' => $this->getAttributeRule($attributeName),
                        'rule' => $this->rule,
                    ]
                );
            }
        }

        return $this->getResponseBuilder()
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
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);
        if (isset($attributeName)) {
            if (($relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName))) && count($relation['attributes']) > 1) {
                [
                    $primaryRelation, $secondaryRelation, $thirdRelation,
                ] = $this->modelRelation->getRelationAttributes($relation);
                $relationAttributeName = $this->field->get($this->modelName, self::FIELD_NAME_RELATION, null);
                if (!isset($relationAttributeName)) {
                    $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $primaryRelation[0]);
                    $items = $this->field->get($this->modelName, $attributeName, []);
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
                    $this->field->set($this->modelName, $attributeName, $items);
                    if ($thirdRelation) {
                        $this->field->set($this->modelName, self::FIELD_NAME_RELATION, $thirdRelation[0]);
                    }

                    return $this->generatePrivateResponse(
                        $this->modelName,
                        $attributeName,
                        [
                            'config' => $this->getAttributeRule($attributeName),
                            'editableRelationId' => $i,
                        ]
                    );
                }
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param int $id Model id
     *
     * @return array
     */
    public function actionUpdate($id = null)
    {
        /* @var ActiveRecord $model */
        $model = $this->getRuleModel($this->rule, $id);

        if (!isset($model)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $buttons = array_map(function (string $attribute) use ($id, $model) {
            return [
                [
                    'text' => Yii::t('bot', $model->getAttributeLabel($attribute)),
                    'callback_data' => self::createRoute('e-a', [
                        'id' => $id,
                        'a' => $attribute,
                    ]),
                ],
            ];
        }, $this->updateAttributes);

        $buttons[] = [
            [
                'text' => Emoji::BACK,
                'callback_data' => self::createRoute('view', [
                    'id' => $model->id,
                ]),
            ],
            [
                'text' => Emoji::DELETE,
                'callback_data' => self::createRoute('delete', [
                    'id' => $model->id,
                ]),
            ],
        ];

        $params = [
            'model' => $model,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render(
                    'view',
                    $this->prepareViewParams($params, $this->rule)
                ),
                $buttons,
                [
                    'disablePreview' => true,
                ]
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
    private function prepareViewParams($params, $rule, $attributeName = null)
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
     * @return string
     */
    public function getModelClass($rule = null)
    {
        if (!$rule) {
            return $this->rule['model'] ?? null;
        }

        if ($this->rule != $rule) {
            Yii::warning('getModelClass: ' . $this->rule['model']);
        }

        return $rule['model'] ?? null;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public function getModelName($modelClass = null)
    {
        if (!$modelClass) {
            $modelClass = $this->modelClass;
        }

        if ($this->modelClass != $modelClass) {
            Yii::warning('getModelName: ' . $modelClass);
        }
        // \yii\helpers\StringHelper::basename($modelClass));
        $parts = explode('\\', $modelClass);

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
        return $this->getResponseBuilder()
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
        if ($this->modelName != $modelName) {
            Yii::warning('fillModel: ' . $modelName);
        }
        if (!$ignoreEditingAttributes && !$editingAttributes) {
            $editingAttributes = $this->getEditingAttributes();
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
                //$model->setAttribute($attributeName, $value ?? null);
                $model->$attributeName = $value ?? null; // Respect model getters and setters
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
     * @param array $config
     *
     * @return array
     */
    private function getAttributeBehaviors($config)
    {
        $behaviors = [];
        $behaviorId = uniqid();
        foreach ($config['behaviors'] ?? [] as $behaviorName => $behaviorValue) {
            $behaviors[$behaviorId . $behaviorName] = $behaviorValue;
        }

        return $behaviors;
    }

    /**
     * @param array $rule
     *
     * @return array
     */
    private function getRuleBehaviors($rule)
    {
        $behaviors = [];
        foreach ($rule['attributes'] as $attributeConfig) {
            $behaviors = array_merge($behaviors, $this->getAttributeBehaviors($attributeConfig));
        }

        return $behaviors;
    }

    /**
     * @param array $rule
     *
     * @return ActiveRecord
     * @throws InvalidConfigException
     */
    private function getFilledModel()
    {
        $id = $this->field->get($this->modelName, self::FIELD_NAME_ID, null);
        $attributeName = $this->field->get($this->modelName, self::FIELD_NAME_ATTRIBUTE, null);
        Yii::warning('getFilledModel attributeName: ' . $attributeName);
        $isNew = is_null($id);
        $manyToManyRelationAttributes = [];
        /* @var ActiveRecord $model */
        if ($isNew) {
            $model = $this->createModel($this->rule);
            foreach ($this->attributes as $attributeName => $config) {
                $this->fillModel(
                    $model,
                    $attributeName,
                    $manyToManyRelationAttributes,
                    compact('config')
                );
            }
            $model->attachBehaviors($this->getRuleBehaviors($this->rule));
        } else {
            $model = $this->getRuleModel($this->rule, $id);
            if ($attributeName) {
                $this->fillModel(
                    $model,
                    $attributeName,
                    $manyToManyRelationAttributes,
                    [
                        'config' => $this->attributes[$attributeName],
                    ]
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
            if (new $secondaryRelation[2]() instanceof DynamicModel) {
                $component = $this->createAttributeComponent($relation);
                $secondaryFieldData = $component->prepare('');
            }
            /** @var ActiveRecord $relationModel */
            $relationModel = new $relation['model']();
            $relationModel->setAttributes([
                $primaryRelation[0] => $model->id,
                $secondaryRelation[0] => $secondaryFieldData,
            ]);

            return $relationModel;
        }

        return null;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws Throwable
     */
    private function save()
    {
        $model = $this->getFilledModel($this->rule);
        $isNew = $model->isNewRecord;

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (isset($this->rule['isVirtual'])) { //Don't save DB record for virtual orders
                    $transaction->commit();
                    return $this->actionView($model->id);
                }

                if ($model->save()) {
                    $relationModel = $this->createRelationModel($model, $this->rule);
                    if ($relationModel && !$relationModel->save()) {
                        throw new \Exception('not possible to save ' . $relationModel->formName() . ' because ' . serialize($relationModel->getErrors()));
                    }
                    foreach ($this->manyToManyRelationAttributes as $attributeName) {
                        $relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName));
                        $relationModelClass = $relation['model'];

                        [
                            $primaryRelation, $secondaryRelation, $thirdRelation,
                        ] = $this->modelRelation->getRelationAttributes($relation);

                        $attributeValues = $this->field->get($this->modelName, $attributeName, []);
                        $appendedIds = [];
                        foreach ($attributeValues as $attributeValue) {
                            if (!$attributeValue) {
                                continue;
                            }
                            $useDynamicModel = false;
                            if (new $secondaryRelation[2]() instanceof DynamicModel) {
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
                                $relationModel = Yii::createObject([
                                    'class' => $relationModelClass,
                                ]);
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
                                    throw new \Exception('not possible to save ' . $relationModel->formName() . ' because ' . serialize($relationModel->getErrors()));
                                }
                            } catch (\yii\db\Exception $exception) {
                                Yii::error('Row in ' . $relationModelClass . ' was not added with attributes ' . serialize($attributeValue) . ' because ' . $exception->getMessage());
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

                    return $this->actionView($model->id);
                }
            } catch (\Exception $e) {
                Yii::warning($e);
                $transaction->rollBack();
            }
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @param ActiveRecord $model
     * @param string $attributeName
     */
    private function getModelDataForAttribute($model, $attributeName)
    {
        $relation = $this->modelRelation->getRelation($this->getAttributeRule($attributeName));
        $data = '';
        if ($relation) {
            $data = [];
            [$primaryRelation, $secondaryRelation] = $this->modelRelation->getRelationAttributes($relation);
            if (!$secondaryRelation) {
                $attributeName = $primaryRelation[0];
                $data[] = [$attributeName => $model->$attributeName];
            }
        } else {
            $data = $model->$attributeName;
        }

        return $data;
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
            $hidden = $assocArray[$nextKey]['hidden'];
            if (is_callable($hidden)) {
                $hidden = call_user_func($hidden, []);
            }

            if ($hidden === true) {
                if (isset($assocArray[$nextKey]['behaviors'])) {
                    $model = $this->getFilledModel($this->rule);
                    $model->validate();
                    $data = $this->getModelDataForAttribute($model, $nextKey);
                    if ($data) {
                        $this->field->set($this->modelName, $nextKey, $data);
                    }
                }
                $nextKey = $this->getNextKey($assocArray, $nextKey);
            }
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
            $hidden = $assocArray[$prevKey]['hidden'];
            if (is_callable($hidden)) {
                $hidden = call_user_func($hidden, $this->getState());
            }

            if ($hidden === true) {
                $prevKey = $this->getPrevKey($assocArray, $prevKey);
            }
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
        if (!$relationAttributeName || isset($config['buttons'])) {
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
        if ((!isset($relationAttributeName) || isset($config['buttonSkip'])) && !$isAttributeRequired && !$isEdit) {
            $buttonSkip = $config['buttonSkip'] ?? [];
            $isPrivateAttribute = $this->attributeButtons->isPrivateAttribute($attributeName, $rule);
            $buttonSkip = ArrayHelper::merge(
                [
                'text' => Yii::t('bot', 'SKIP'),
                'callback_data' => self::createRoute($isPrivateAttribute ? 's-a' : 'en-a', [
                    'a' => $attributeName,
                    'text' => self::VALUE_NO,
                ]),
            ],
                $buttonSkip
            );

            $systemButtons[] = $buttonSkip;
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
        $error = ArrayHelper::getValue($options, 'error', null);
        $editableRelationId = ArrayHelper::getValue($options, 'editableRelationId', null);

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
                $nextAttribute = $this->getNextKey($this->attributes, $attributeName);

                if (isset($nextAttribute)) {
                    return $this->generateResponse($modelName, $nextAttribute, $this->rule);
                }
            }
            /* @var ActiveQuery $query */
            $query = call_user_func([$relationAttribute[0], 'find'], [$this->getState()]);
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
                            'text' => $model->getLabel(),
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
                compact('isEmpty', 'modelId', 'editableRelationId')
            );
            $buttons = $this->prepareButtons(
                $itemButtons,
                $systemButtons,
                compact('config', 'isEmpty', 'modelName', 'attributeName')
            );
            $model = $this->getFilledModel($this->rule);

            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->renderAttribute(
                        $this->prepareViewFileName($attributeName, compact('relationAttributeName')),
                        [
                            'step' => $step,
                            'error' => $error,
                            'totalSteps' => $totalSteps,
                            'isEdit' => $isEdit,
                            'model' => $model,
                            'relationModel' => $relationModel,
                        ],
                        [
                            'rule' => $this->rule,
                            'attributeName' => $attributeName,
                        ]
                    ),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
                ->build();
        }

        $isAttributeRequired = $config['isRequired'] ?? true;
        $rule = $this->rule;

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
                        $label = $model->getLabel();
                        $id = $model->id;
                    } else {
                        $label = $item;
                    }
                    if (!$id) {
                        $id = 'v_' . $key;
                    }
                } catch (\Exception $e) {
                    Yii::warning($e);
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
                compact('isEmpty', 'modelId')
            ),
            compact('isEmpty', 'config', self::FIELD_NAME_ATTRIBUTE, 'modelName')
        );
        $model = $this->getFilledModel($rule);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->renderAttribute(
                    $this->prepareViewFileName($attributeName),
                    [
                        'step' => $step,
                        'error' => $error,
                        'totalSteps' => $totalSteps,
                        'isEdit' => $isEdit,
                        'model' => $model,
                    ],
                    [
                        'rule' => $this->rule,
                        'attributeName' => $attributeName,
                    ]
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
    ) {
        $config = ArrayHelper::getValue($options, 'config', []);
        $rule = $this->getRule($modelName);
        Yii::warning('generatePublicResponse: ' . $modelName);
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
            compact('isEmpty', 'modelId')
        );
        [$step, $totalSteps] = $this->getStepsInfo($attributeName, $this->getRule($modelName));
        $buttons = $this->prepareButtons(
            [],
            $systemButtons,
            compact('config', 'isEmpty', 'modelName', 'attributeName')
        );
        $model = $this->getFilledModel($rule);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->renderAttribute(
                    $this->prepareViewFileName($attributeName),
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
                [
                    'disablePreview' => true,
                ]
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
        Yii::warning('generateResponse modelName: ' . $modelName);

        //$rule = ArrayHelper::getValue($options, 'rule', []);
        $config = $this->rule['attributes'][$attributeName];
        if ($this->attributeButtons->isPrivateAttribute($attributeName, $this->rule)) {
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
                compact('config')
            );

            if (($config['createRelationIfEmpty'] ?? false) && !$this->modelRelation->filledRelationCount($attributeName)) {
                return $this->actionAA($attributeName);
            }

            return $response;
        } else {
            return $this->generatePublicResponse(
                $modelName,
                $attributeName,
                compact('config')
            );
        }
    }

    /**
     * If you call directly - you should use remove array keys
     *
     * @return array ['back => ['text' => 'this is text', 'callback_data' => 'route']]
     */
    private function getDefaultSystemButtons($isEdit)
    {
        if ($isEdit && $this->enableGlobalBackRoute ) {
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

        if (!$isEdit && $this->enableEndRoute && ($endRoute = $this->endRoute->get())) {
            /* 'End' button */
            $systemButtons['end'] = [
                'text' => Emoji::END,
                'callback_data' => $endRoute,
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
        if (isset($this->rule['isVirtual']) && !empty($this->getEditingAttributes())) {
            $isEdit = true;
        }

        $config = $this->getAttributeRule($attributeName);
        $isFirstScreen = !strcmp($attributeName, array_key_first($this->attributes));
        if ($isFirstScreen || $isEdit) {
            $this->enableGlobalBackRoute = true;
        }
        $systemButtons = $this->getDefaultSystemButtons($isEdit);
        $configSystemButtons = $this->attributeButtons->getSystems($this->rule, $attributeName, $modelId);
        $editingAttributes = $this->getEditingAttributes();
        if (!$isEdit && $editingAttributes && ($prevAttribute = $this->getPrevKey($editingAttributes, $attributeName))) {
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
            unset($systemButtons['back']);
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
        $totalSteps = count($this->attributes);
        $step = array_search($attributeName, array_keys($this->attributes)) + 1;

        return [$step, $totalSteps];
    }

    /**
     * @param array $rule
     *
     * @return string
     */
    public function getModelClassByRule($rule)
    {
        if ($this->rule['model'] != $rule['model']) {
            Yii::warning('getModelClassByRule: ' . $rule['model']);
        }

        return $rule['model'];
    }

    /**
     * @param array $rule
     *
     * @return object|null
     */
    private function createModel(array $rule)
    {
        try {
            $object = Yii::createObject([
                'class' => $this->modelClass,
            ]);
            if ($object instanceof ActiveRecord) {
                return $object;
            }

            return null;
        } catch (\Throwable $e) {
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
        if ($this->rule != $rule) {
            Yii::warning('getRuleModel: ' . $this->getModelClass($rule));
        }
        $model = call_user_func([$this->getModelClass($rule), 'findOne'], $id);

        return $model ?? null;
    }

    /**
     * @param string $attributeName
     *
     * @return array
     */
    public function getAttributeRule(string $attributeName)
    {
        if (array_key_exists($attributeName, $this->attributes)) {
            return $this->attributes[$attributeName];
        }

        return [];
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

        if ($this->getModelName($this->getModelClassByRule($this->rule)) == $modelName) {
            $requestedRule = $this->rule;
        }

        if ($this->modelName != $modelName) {
            Yii::warning('getRule: ' . $modelName);
        }

        return $requestedRule;
    }

    /**
     * @param string $attributeName
     *
     * @return boolean
     */
    private function canSkipAttribute(string $attributeName)
    {
        $config = $this->getAttributeRule($attributeName);
        $isRequired = $config['isRequired'] ?? true;
        $isEmptyAttribute = empty($this->field->get($this->modelName, $attributeName, null));

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
        $stateRoute = $state->getName();
        if (isset($stateRoute)) {
            $stateRequest = Request::fromUrl($stateRoute);
        }
        if (isset($stateRequest)) {
            $stateAttributeName = $stateRequest->getParam('a', null);
        }
        if (isset($stateAttributeName) && array_key_exists($attributeName, $this->attributes)) {
            if ($stateAttributeName == $attributeName) {
                return true;
            }
            if ($this->canSkipAttribute($stateAttributeName)
                && $this->getNextKey($this->attributes, $stateAttributeName) == $attributeName) {
                return true;
            }
            if ($this->getPrevKey($this->attributes, $stateAttributeName) == $attributeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $attributeName
     * @param array $options
     *
     * @return string
     */
    private function prepareViewFileName(string $attributeName, $options = [])
    {
        $relationAttributeName = ArrayHelper::getValue($options, 'relationAttributeName', null);
        $config = $this->getAttributeRule($attributeName);

        if (isset($config['view'])) {
            return $config['view'];
        }

        if ($relationAttributeName && ($config['enableAddButton'] ?? false)) {
            $pathArray = [
                $attributeName,
                '/set-',
                $relationAttributeName,
            ];
        } else {
            $pathArray = [
                'set-',
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
}
