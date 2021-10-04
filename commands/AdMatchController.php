<?php

namespace app\commands;

use app\models\matchers\AdOfferMatcher;
use app\models\matchers\AdSearchMatcher;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\AdOffer;
use app\models\AdSearch;
use yii\console\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdMatchController
 *
 * @package app\commands
 */
class AdMatchController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->update();
    }

    public function actionClearMatches()
    {
        Yii::$app->db->createCommand()
            ->truncateTable('{{%ad_offer_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->truncateTable('{{%ad_search_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%ad_search}}', [
                'processed_at' => null,
            ])
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%ad_offer}}', [
                'processed_at' => null,
            ])
            ->execute();
    }

    protected function update()
    {
        $this->updateAdOffers();
        $this->updateAdSearches();
    }

    protected function updateAdOffers()
    {
        $updatesCount = 0;

        $adOfferQuery = $this->getAdOfferQuery();

        /** @var AdOffer $adOffer */
        foreach ($adOfferQuery->all() as $adOffer) {
            try {
                $matchedCount = (new AdOfferMatcher($adOffer))->match();

                $adOffer->setAttributes([
                    'processed_at' => time(),
                ]);
                $adOffer->save();
                $updatesCount++;

                $this->printMatchedCount($adOffer, $matchedCount);
            } catch (\Exception $e) {
                echo 'ERROR: AdOffer #' . $adOffer->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Offers processed: ' . $updatesCount);
        }
    }

    protected function updateAdSearches()
    {
        $updatesCount = 0;

        $adSearchQuery = $this->getAdSearchQuery();

        /** @var AdSearch $adSearch */
        foreach ($adSearchQuery->all() as $adSearch) {
            try {
                $matchedCount = (new AdSearchMatcher($adSearch))->match();

                $adSearch->setAttributes([
                    'processed_at' => time(),
                ]);

                $adSearch->save();
                $updatesCount++;

                $this->printMatchedCount($adSearch, $matchedCount);
            } catch (\Exception $e) {
                echo 'ERROR: AdSearch #' . $adSearch->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Searches processed: ' . $updatesCount);
        }
    }

    private function getAdOfferQuery(): ActiveQuery
    {
        return AdOffer::find()
            ->where([AdOffer::tableName() . '.processed_at' => null])
            ->andWhere([AdOffer::tableName() . '.status' => AdOffer::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->orderBy([
                'user.rating' => SORT_DESC,
                'user.created_at' => SORT_ASC,
            ]);
    }

    private function getAdSearchQuery(): ActiveQuery
    {
        return AdSearch::find()
            ->where([AdSearch::tableName() . '.processed_at' => null])
            ->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->orderBy([
                'user.rating' => SORT_DESC,
                'user.created_at' => SORT_ASC,
            ]);
    }

    private function printMatchedCount(ActiveRecord $model, int $count)
    {
        if ($count) {
            $this->output(get_class($model) . ' matches added: ' . $count);
        }
    }
}
