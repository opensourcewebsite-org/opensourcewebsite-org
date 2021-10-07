<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\models\CurrencyExchangeOrder;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;
use yii\db\Expression;
use app\models\queries\CurrencyExchangeOrderQuery;
use yii\helpers\ArrayHelper;

final class CurrencyExchangeOrderMatcher
{
    private CurrencyExchangeOrder $model;
    private ModelLinker $linker;
    private string $comparingTable;

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = CurrencyExchangeOrder::tableName();
    }

    public function match(): int
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();

        $buyingMethodsIds = ArrayHelper::getColumn($this->model->getBuyingPaymentMethods()->asArray()->all(), 'id');
        $sellingMethodsIds = ArrayHelper::getColumn($this->model->getSellingPaymentMethods()->asArray()->all(), 'id');

        $matchesQuery
            ->joinWith('sellingPaymentMethods sm')
            ->joinWith('buyingPaymentMethods bm');

        if ($this->model->selling_cash_on && $this->model->selling_location_lat && $this->model->selling_location_lon) {
            $matchesQuery->andWhere(
                ['or',
                    ['and',
                        ['buying_cash_on' => true],
                        "ST_Distance_Sphere(
                            POINT({$this->model->selling_location_lon}, {$this->model->selling_location_lat}),
                            POINT({$this->comparingTable}.buying_location_lon, {$this->comparingTable}.buying_location_lat)
                            ) <= 1000 * ({$this->comparingTable}.buying_delivery_radius + " . ($this->model->selling_delivery_radius ?: 0) . ')'
                    ],
                    ['in', 'sm.id', $buyingMethodsIds]
                ]
            );
        } else {
            $matchesQuery->andWhere(['in', 'sm.id', $buyingMethodsIds]);
        }

        if ($this->model->buying_cash_on && $this->model->buying_location_lat && $this->model->buying_location_lon) {
            $matchesQuery->andWhere(
                ['or',
                    ['and',
                        ['selling_cash_on' => true],
                        "ST_Distance_Sphere(
                            POINT({$this->model->buying_location_lon}, {$this->model->buying_location_lat}),
                            POINT({$this->comparingTable}.selling_location_lon, {$this->comparingTable}.selling_location_lat)
                        ) <= 1000 * ({$this->comparingTable}.selling_delivery_radius + " . ($this->model->buying_delivery_radius ?: 0) . ')'
                    ],
                    ['in', 'bm.id', $sellingMethodsIds]
                ]
            );
        } else {
            $matchesQuery->andWhere(['in', 'bm.id', $sellingMethodsIds]);
        }

        $matchesQuery->andWhere(['<=', 'fee', (-1 * (float)$this->model->fee)]);

        $matches = $matchesQuery->all();
        $matchesCount = count($matches);

        $this->linker->linkMatches($matches);
        $this->linker->linkCounterMatches($matches);

        return $matchesCount;
    }

    private function prepareMainQuery(): CurrencyExchangeOrderQuery
    {
        return CurrencyExchangeOrder::find()
            ->excludeUserId($this->model->user_id)
            ->live()
            ->andWhere(["{$this->comparingTable}.buying_currency_id" => $this->model->selling_currency_id])
            ->andWhere(["{$this->comparingTable}.selling_currency_id" => $this->model->buying_currency_id]);
    }
}
