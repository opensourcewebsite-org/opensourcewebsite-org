<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\modules\bot\models\AdOffer;
use app\modules\bot\models\AdSearch;

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

    protected function update()
    {
        $this->updateAdOffers();
        $this->updateAdSearches();
    }

    protected function updateAdSearches()
    {
        $updatesCount = 0;

        $adSearchQuery = AdSearch::find()
            ->where([AdSearch::tableName() . '.processed_at' => null])
            ->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($adSearchQuery->all() as $adSearch) {
            $updatesCount++;
            $adSearch->updateMatches();

            $adSearch->setAttributes([
                'processed_at' => time(),
            ]);
            $adSearch->save();
        }

        if ($updatesCount) {
            $this->output('Searches updated: ' . $updatesCount);
        }
    }

    protected function updateAdOffers()
    {
        $updatesCount = 0;

        $adOfferQuery = AdOffer::find()
            ->where([AdOffer::tableName() . '.processed_at' => null])
            ->andWhere([AdOffer::tableName() . '.status' => AdOffer::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($adOfferQuery->all() as $adOffer) {
            $updatesCount++;
            $adOffer->updateMatches();

            $adOffer->setAttributes([
                'processed_at' => time(),
            ]);
            $adOffer->save();
        }

        if ($updatesCount) {
            $this->output('Offers updated: ' . $updatesCount);
        }
    }

    public function actionClearMatches()
    {
        Yii::$app->db->createCommand()
            ->truncateTable('{{%ad_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update(
                '{{%ad_search}}',
                ['processed_at' => null])
            ->execute();

        Yii::$app->db->createCommand()
            ->update(
                '{{%ad_offer}}',
                ['processed_at' => null])
            ->execute();
    }
}
