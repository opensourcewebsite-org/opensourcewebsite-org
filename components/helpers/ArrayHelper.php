<?php

namespace app\components\helpers;

use yii;

class ArrayHelper extends yii\helpers\ArrayHelper
{

    /**
     * @param array|Object[] $items
     * @param string $keyField
     * @return array
     */
    public static function buildIndex($items, $keyField = 'id')
    {
        $result = [];
        foreach ($items as $item) {
            $key = self::_getObjectFieldValue($item, $keyField);
            if ($key !== null) {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    /**
     * @param array|Object[] $items
     * @param string $keyField
     * @return array
     */
    public static function buildIndexWithArrays(array $items, $keyField = 'id')
    {
        $result = [];
        foreach ($items as $item) {
            $key = self::_getObjectFieldValue($item, $keyField);
            if ($key !== null) {
                if (!in_array(gettype($key), ['string', 'integer'])) {
                    $key = (string)$key;
                }
                if (!array_key_exists($key, $result)) {
                    $result[$key] = [];
                }
                $result[$key][] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array $items
     * @param callable $boolFunc
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function findFirst(array $items, callable $boolFunc, $defaultValue = null)
    {
        foreach ($items as $item) {
            if ($boolFunc($item)) {
                return $item;
            }
        }
        return $defaultValue;
    }

    /**
     * @param array $items
     * @param callable $boolFunc
     * @return bool
     */
    public static function isEvery(array $items, callable $boolFunc)
    {
        foreach ($items as $item) {
            if (!$boolFunc($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param yii\base\Model $object
     * @param string $fieldName
     * @return mixed
     */
    private static function _getObjectFieldValue($object, $fieldName)
    {
        $isArrayKeyExistAndScalar = is_array($object) && array_key_exists($fieldName, $object) && is_scalar($object[$fieldName]);
        $isObjectKeyExistAndScalar = is_object($object) && array_key_exists($fieldName, get_object_vars($object)) && is_scalar($object->$fieldName);
        $isYiiModelKeyExistAndScalar = is_object($object) && array_key_exists($fieldName, array_flip($object->attributes())) && is_scalar($object->$fieldName);
        $isObjectMethodExistAndScalar = is_object($object) && method_exists($object, $fieldName) && is_scalar($object->$fieldName());
        $getMethodName = 'get' . ucfirst($fieldName);
        $isObjectGetMethodExistAndScalar = is_object($object) && method_exists($object, $getMethodName) && is_scalar($object->$getMethodName());

        $value = null;
        if ($isYiiModelKeyExistAndScalar || $isObjectKeyExistAndScalar) {
            $value = $object->$fieldName;
        }

        if ($isArrayKeyExistAndScalar) {
            $value = $object[$fieldName];
        }

        if ($isObjectMethodExistAndScalar) {
            $value = $object->$fieldName();
        }

        if ($isObjectGetMethodExistAndScalar) {
            $value = $object->$getMethodName();
        }

        return $value;
    }
}
