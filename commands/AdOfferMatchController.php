<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\AdOffer;
use app\models\matchers\AdOfferMatcher;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdOfferMatchController
 *
 * @package app\commands
 */
class AdOfferMatchController extends Controller implements CronChainedInterface
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

        /** @var AdOffer $adOffer */
        foreach ($query->all() as $model) {
            try {
                $matchesCount = (new AdOfferMatcher($model))->match();

                $model->setAttributes([
                    'processed_at' => time(),
                ]);

                $model->save();

                $this->printMatchedCount($model, $matchesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: AdOffer #' . $model->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Offers processed: ' . $updatesCount);
        }
    }

    private function getQuery(): ActiveQuery
    {
        return AdOffer::find()
            ->where([AdOffer::tableName() . '.processed_at' => null])
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
            ->truncateTable('{{%ad_offer_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%ad_offer}}', [
                'processed_at' => null,
            ])
            ->execute();
    }
}
