<?php

namespace app\models\queries\traits;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

trait SelfSearchTrait
{
    /**
     * @param ActiveRecord[] $models
     * @param string $operand
     *
     * @return ActiveQuery|self
     */
    public function models(array $models, string $operand = 'IN'): ActiveQuery
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $table = $class::tableName();

        $columns = [];
        foreach ($class::primaryKey() as $attribute) {
            $columns[] = "$table.$attribute";
        }

        $params = [];
        foreach ($models as $balance) {
            $pk = [];
            foreach ($balance->getPrimaryKey(true) as $attribute => $value) {
                $pk["$table.$attribute"] = $value;
            }
            $params[] = $pk;
        }

        return $this->andWhere([$operand, $columns, $params]);
    }
}
