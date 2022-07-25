<?php

declare(strict_types=1);

namespace app\models\queries\builders;

use app\models\interfaces\ModelWithLocationInterface;
use yii\db\Expression;

class RadiusExpressionBuilder implements ConditionExpressionBuilderInterface
{
    private ModelWithLocationInterface $model;
    private string $targetTableName;

    public function __construct(ModelWithLocationInterface $model, string $targetTableName)
    {
        $this->model = $model;
        $this->targetTableName = $targetTableName;
    }

    public function build(): Expression
    {
        if ($this->model->getLocation()) {
            return $this->prepareRadiusExpression();
        }

        return new Expression('');
    }

    private function prepareRadiusExpression(): Expression
    {
        $tableName = $this->targetTableName;

        return new Expression(
            "IF(($tableName.location_lon AND $tableName.location_lat AND $tableName.search_radius > 0),
                    ST_Distance_Sphere(
                        POINT({$this->model->location_lon}, {$this->model->location_lat}),
                        POINT($tableName.location_lon, $tableName.location_lat))
                        ,0)
                    <= (1000 * $tableName.search_radius)"
        );
    }
}
