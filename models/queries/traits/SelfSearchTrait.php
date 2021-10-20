<?php

namespace app\models\queries\traits;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

trait SelfSearchTrait
{
    /**
     * @param ActiveRecord[] $models
     * @param string $operand
     * @param array $attributes  default - ActiveRecord::primaryKey()
     *
     * @return ActiveQuery|self
     */
    public function models(array $models, string $operand = 'IN', $attributes = []): ActiveQuery
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $table = $class::tableName();

        if (empty($attributes)) {
            $attributes = $class::primaryKey();
        }

        $params = [];
        $paramsNull = [];
        foreach ($models as $model) {
            $modelCondition = [];
            $isNull = false;

            foreach ($attributes as $attribute) {
                $value = $model->getAttribute($attribute);
                if ($value === null) {
                    $isNull = true;
                }
                $modelCondition["$table.$attribute"] = $value;
            }

            if ($isNull) {
                $paramsNull[] = $modelCondition;
            } else {
                $params[] = $modelCondition;
            }
        }

        $columns = [];
        foreach ($attributes as $attribute) {
            $columns[] = "$table.$attribute";
        }

        $this->andWhere([$operand, $columns, $params]);

        if (empty($paramsNull)) {
            return $this;
        }

        $conditionOr = ['OR'];
        foreach ($paramsNull as $modelCondition) {
            $conditionAnd = ['AND'];
            foreach ($modelCondition as $attribute => $value) {
                $conditionAnd[] = ($value === null) ? "$attribute IS NULL" : [$attribute => $value];
            }
            $conditionOr[] = $conditionAnd;
        }

        return $this->orWhere($conditionOr);
    }
}
