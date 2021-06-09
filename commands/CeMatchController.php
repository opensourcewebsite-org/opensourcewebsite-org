<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\CurrencyExchangeOrder;
use yii\console\Exception;

/**
 * Class CeMatchController
 *
 * @package app\commands
 */
class CeMatchController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->update();
    }

    protected function update()
    {
        $this->updateOrders();
    }

    protected function updateOrders()
    {
        $updatesCount = 0;

        $orderQuery = CurrencyExchangeOrder::find()
            ->where([CurrencyExchangeOrder::tableName() . '.processed_at' => null])
            ->andWhere([CurrencyExchangeOrder::tableName() . '.status' => CurrencyExchangeOrder::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - CurrencyExchangeOrder::LIVE_DAYS * 24 * 60 * 60])
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($orderQuery->all() as $order) {
            try {
                $order->updateMatches();

                $order->setAttributes([
                    'processed_at' => time(),
                ]);
                $order->save();
                $updatesCount++;
            } catch (Exception $e) {
                echo 'ERROR: Order #' . $order->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Orders processed: ' . $updatesCount);
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
