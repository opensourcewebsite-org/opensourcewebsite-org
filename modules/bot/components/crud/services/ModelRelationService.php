<?php

namespace app\modules\bot\components\crud\services;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\crud\CrudController;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class ModelRelationService
 *
 * @package app\modules\bot\components\crud\services
 */
class ModelRelationService
{
    /** @var Controller */
    public $controller;

    /**
     * @param $modelClass
     * @param $attributes
     *
     * @return ActiveRecord
     */
    public function fillModel($modelClass, $attributes)
    {
        if (!is_array($attributes)) {
            return null;
        }

        return new $modelClass($attributes);
    }

    /**
     * Возвращает связанные модели
     *
     * @param ActiveRecord $model
     * @param $attributeName
     *
     * @return ActiveRecord[]
     */
    public function findAll(ActiveRecord $model, $attributeName)
    {
        $relation = $this->getRelation($this->controller->rule['attributes'][$attributeName]);
        [$primaryRelation] = $this->getRelationAttributes($relation);

        return call_user_func([$relation['model'], 'findAll'], [$primaryRelation[0] => $model->id]);
    }

    /**
     * @param $attributeName
     * @param int|null $modelId
     *
     * @return int
     */
    public function filledRelationCount($attributeName, $modelId = null)
    {
        $rule = $this->controller->rule;
        $modelName = $this->controller->getModelName($rule['model']);
        if (!$modelId) {
            $modelId = $this->controller->field->get($modelName, CrudController::FIELD_NAME_ID, null);
        }
        if ($modelId) {
            $model = $this->controller->getRuleModel($rule, $modelId);
            $relationData = $this->findAll($model, $attributeName);
        } else {
            $relationData = $this->controller->field->get($modelName, $attributeName, []);
            $relationData = $this->prepareRelationData($attributeName, $relationData);
        }

        return count($relationData);
    }

    /**
     * @param array $relationData
     *
     * @return array
     */
    public function prepareRelationData($attributeName, $relationData)
    {
        $rule = $this->controller->rule;
        $attributes = $this->controller->getAttributes($rule);
        $relation = $this->getRelation($attributes[$attributeName]);
        [, , $thirdRelation] = $this->getRelationAttributes($relation);

        $relationData = array_filter(
            $relationData,
            function ($val) use ($thirdRelation) {
                if ($thirdRelation && !($val[$thirdRelation[0]] ?? false)) {
                    return false;
                }

                return $val;
            }
        );


        return array_values($relationData);
    }

    /**
     * @param $attributeConfig
     * @param $id
     *
     * @return mixed
     */
    public function getFirstModel($attributeConfig, $id)
    {
        [$primaryRelation] = $this->getRelationAttributes($this->getRelation($attributeConfig));
        if (!$primaryRelation) {
            return null;
        }

        return call_user_func([$primaryRelation[2], 'findOne'], $id);
    }

    /**
     * @param $attributeConfig
     * @param $id
     *
     * @return mixed
     */
    public function getSecondModel($attributeConfig, $id)
    {
        [, $secondaryRelation] = $this->getRelationAttributes($this->getRelation($attributeConfig));
        if (!$secondaryRelation) {
            return null;
        }

        return call_user_func([$secondaryRelation[2], 'findOne'], $id);
    }

    /**
     * @param $attributeConfig
     * @param $id
     *
     * @return mixed
     */
    public function getThirdModel($attributeConfig, $id)
    {
        [, , $thirdRelation] = $this->getRelationAttributes($this->getRelation($attributeConfig));
        if (!$thirdRelation) {
            return null;
        }

        return call_user_func([$thirdRelation[2], 'findOne'], $id);
    }

    /**
     * @param array $rule
     * @param array $relation
     * @param integer $id
     * @param integer $secondId
     *
     * @return ActiveRecord
     * @throws Exception
     */
    public function getMainModel($relation, $id, $secondId)
    {
        [$primaryRelation, $secondaryRelation] = $this->getRelationAttributes($relation);
        $modelClass = $relation['model'] ?? null;
        $conditions = [];
        $conditions[$primaryRelation[0]] = $id;
        $conditions[$secondaryRelation[0]] = $secondId;
        $model = call_user_func([$modelClass, 'findOne'], $conditions);
        /* @var ActiveRecord $model */
        if (!$model) {
            throw new \Exception($modelClass . ' with params ' . serialize($conditions) . ' was not found');
        }

        return $model;
    }

    /**
     * @param                     $relation
     *
     * @return array [['column_id', 'ref_column_id', 'class'], ['sec_column_id','sec_ref_column_id', 'class', ?'field']]
     */
    public function getRelationAttributes($relation)
    {
        $className = $this->controller->getModelClassByRule($this->controller->rule);
        $relationAttributes = $relation['attributes'] ?? [];
        $primaryRelation = [];
        $secondaryRelation = [];
        $thirdRelation = [];
        foreach ($relationAttributes as $relationKey => $relationAttribute) {
            if (strcmp($className, $relationAttribute[0])) {
                if ($secondaryRelation) {
                    $thirdRelation = [];
                    $thirdRelation[] = $relationKey;
                    $thirdRelation[] = $relationAttribute[1];
                    $thirdRelation[] = $relationAttribute[0];
                    if (isset($relationAttribute[2])) {
                        $thirdRelation[] = $relationAttribute[2];
                    }
                } else {
                    $secondaryRelation = [];
                    $secondaryRelation[] = $relationKey;
                    $secondaryRelation[] = $relationAttribute[1];
                    $secondaryRelation[] = $relationAttribute[0];
                    if (isset($relationAttribute[2])) {
                        $secondaryRelation[] = $relationAttribute[2];
                    }
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

        return [$primaryRelation, $secondaryRelation, $thirdRelation];
    }

    /**
     * If attribute has relation, this method perform its validation and return an array on success, otherwise null.
     *
     * @param array $attributeConfig
     *
     * @return array|null
     */
    public function getRelation($attributeConfig)
    {
        if (!is_array($attributeConfig)) {
            return null;
        }
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
                } catch (InvalidConfigException $e) {
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
}
