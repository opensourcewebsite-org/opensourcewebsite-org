<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderMatch;
use app\models\matchers\CurrencyExchangeOrderMatcher;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
            ->truncateTable(CurrencyExchangeOrderMatch::tableName())
            ->execute();

        Yii::$app->db->createCommand()
            ->update(CurrencyExchangeOrder::tableName(), [
                'processed_at' => null,
            ])
            ->execute();
    }
}
