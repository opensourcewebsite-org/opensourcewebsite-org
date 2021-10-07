<?php

namespace app\commands;

use app\models\matchers\CurrencyExchangeOrderMatcher;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\CurrencyExchangeOrder;
use yii\console\Exception;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class CurrencyExchangeOrderMatchController
 *
 * @package app\commands
 */
class CurrencyExchangeOrderMatchController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->update();
    }

    protected function update()
    {
        $updatesCount = 0;

        $query = $this->getQuery();

        /** @var CurrencyExchangeOrder $model */
        foreach ($query->all() as $model) {
            try {
                $matchesCount = (new CurrencyExchangeOrderMatcher($model))->match();
                // TODO refactoring
                //$model->updateMatches();

                $model->setAttributes([
                    'processed_at' => time(),
                ]);

                $model->save();

                $this->printMatchedCount($model, $matchesCount);

                $updatesCount++;
            } catch (Exception $e) {
                echo 'ERROR: Order #' . $model->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Orders processed: ' . $updatesCount);
        }
    }

    private function getQuery(): ActiveQuery
    {
        return CurrencyExchangeOrder::find()
            ->where([CurrencyExchangeOrder::tableName() . '.processed_at' => null])
            ->live()
            ->orderByRank();
    }

    private function printMatchedCount(ActiveRecord $model, int $count)
    {
        if ($count) {
            $this->output(get_class($model) . ' matches added: ' . $count);
        }
    }

    public function actionClearMatches()
    {
        Yii::$app->db->createCommand()
            ->truncateTable('{{%currency_exchange_order_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%currency_exchange_order}}', [
                'processed_at' => null,
            ])
            ->execute();
    }
}
