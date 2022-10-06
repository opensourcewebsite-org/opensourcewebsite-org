<?php

namespace app\models\queries\traits;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

trait RandomTrait
{
    /**
     * <b>NOTE:</b>
     * 1. all Query::$params must be set BEFORE this method
     * 2. If you will add any condition AFTER this method - query may find 0 rows with some chance.
     *
     * So call this method right before querying.
     *
     * * <b>Description:</b> This method uses ORDER BY RAND(), but only on a small percentage of the table rows; this percentage is based upon how many rows you want, LIMIT 1, divided by how many rows the table has, COUNT(*), and then multiply that figure by 10 to avoid returning less rows than you request.
     * * <b>Advantage:</b> Easy to use in complicated SQL queries and you don’t need to have a sequentially numbered field in your table. Easily select multiple random rows by simply increasing the LIMIT and adjusting the WHERE statement to match.
     * * <b>Disadvantage:</b> This method’s speed is directly related to how long it takes to generate a random value for each row you query. The more random rows you want, the longer it takes.
     *
     * @param int $limit
     * @return ActiveQuery
     * @link https://www.warpconduit.net/2011/03/23/selecting-a-random-record-using-mysql-benchmark-results/
     */
    public function orderByRandAlt($limit = 1): ActiveQuery
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        $table = $modelClass::tableName();

        $where = $modelClass::getDb()->getQueryBuilder()->buildWhere($this->where, $this->params);

        return $this->andWhere("RAND() < (SELECT (($limit/COUNT(*))*10) FROM $table $where)")
            ->orderBy('RAND()')
            ->limit($limit);
    }
}
