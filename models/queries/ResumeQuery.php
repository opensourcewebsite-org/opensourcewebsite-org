<?php


namespace app\models\queries;

use app\models\Resume;
use app\models\Vacancy;
use yii\db\ActiveQuery;
use yii\db\conditions\OrCondition;
use yii\db\Expression;

/**
 * Class ResumeQuery
 *
 * @package app\models\queries
 */
class ResumeQuery extends ActiveQuery
{
    /**
     * @return ResumeQuery
     */
    public function active()
    {
        return $this->andWhere([Resume::tableName() . '.status' => Resume::STATUS_ON])
            ->andWhere(['>=', Resume::tableName() . '.renewed_at', time() - Resume::LIVE_DAYS * 24 * 60 * 60]);
    }

    /**
     * совпадения по локации и радиусу поиска,
     * в случае если удаленка выключена в одном из обьектов или в обоих.
     * если в обоих обьектах включена удаленка - то они найдутся
     *
     * @param Vacancy $model
     *
     * @return $this
     */
    public function matchRadius(Vacancy $model)
    {
        $radiusExpression = '';
        if ($model->location_lat && $model->location_lon) {
            $radiusExpression = new Expression(
                "IF((" . Resume::tableName() . ".location_lon AND " . Resume::tableName() . ".location_lat)," .
                "ST_Distance_Sphere(" .
                "POINT($model->location_lon, $model->location_lat), " .
                "POINT(" . Resume::tableName() . ".location_lon, " . Resume::tableName() . ".location_lat)" .
                "),0) <= (1000 * " . Resume::tableName() . ".search_radius)"
            );
        }
        if ($model->remote_on == Vacancy::REMOTE_ON) {
            $remoteCondition = [Resume::tableName() . '.remote_on' => Resume::REMOTE_ON];
            if ($radiusExpression) {
                $this->andWhere(new OrCondition([$remoteCondition, $radiusExpression]));
            } else {
                $this->andWhere($remoteCondition);
            }
        } elseif ($radiusExpression) {
            $this->andWhere($radiusExpression);
        }

        return $this;
    }

    /**
     * совпадения по локации и радиусу поиска,
     * в случае если удаленка выключена в одном из обьектов или в обоих.
     * если в обоих обьектах включена удаленка - то они найдутся
     *
     * @param Vacancy $model
     *
     * @return $this
     */
    public function matchRadius(Vacancy $model)
    {
        $radiusExpression = '';
        if ($model->location_lat && $model->location_lon) {
            $radiusExpression = new Expression(
                'IF((' . Resume::tableName() . '.location_lon AND ' . Resume::tableName() . '.location_lat),' .
                'ST_Distance_Sphere(' .
                'POINT(' . $model->location_lon . ', ' . $model->location_lat . '), ' .
                'POINT(' . Resume::tableName() . '.location_lon, ' . Resume::tableName() . '.location_lat)' .
                '),0) <= (1000 * ' . Resume::tableName() . '.search_radius)'
            );
        }
        if ($model->remote_on == Vacancy::REMOTE_ON) {
            $remoteCondition = [Resume::tableName() . '.remote_on' => Resume::REMOTE_ON];
            if ($radiusExpression) {
                $this->andWhere(new OrCondition([$remoteCondition, $radiusExpression]));
            } else {
                $this->andWhere($remoteCondition);
            }
        } elseif ($radiusExpression) {
            $this->andWhere($radiusExpression);
        }

        return $this;
    }
}
