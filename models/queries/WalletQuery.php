<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\Currency;
use yii\db\ActiveQuery;

class WalletQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function orderByCurrencyCode(): self
    {
        return $this->joinWith('currency')
            ->orderBy([
                Currency::tableName() . '.code' => SORT_ASC,
            ]);
    }
}
