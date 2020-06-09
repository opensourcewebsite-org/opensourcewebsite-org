<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\console\Controller;
use yii\httpclient\Client;
use app\modules\bot\models\AdOffer;
use app\modules\bot\models\AdSearch;

class AdMatchesController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->output('Running ad matches...');
        $this->update();
    }

    protected function update()
    {
        $this->output('Runnning update...');

        $this->updateAdOffers();
        $this->updateAdSearches();
    }

    protected function updateAdSearches()
    {
        $this->output('Running updateAdSearch...');

        $adSearchQuery = AdSearch::find()
            ->where(['processed_at' => null])
            ->andWhere(['>=', 'renewed_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(['status' => AdSearch::STATUS_ON])
            ->joinWith('globalUser')
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($adSearchQuery->all() as $adSearch) {
            $adSearch->updateMatches();
        }
    }

    protected function updateAdOffers()
    {
        $this->output('Running updateAdOffer...');

        $adOfferQuery = AdOffer::find()
            ->where(['processed_at' => null])
            ->andWhere(['>=', 'renewed_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->andWehre(['status' => AdOffer::STATUS_ON])
            ->joinWith('globalUser')
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($adOfferQuery->all() as $adOffer) {
            $adOffer->updateMatches();
        }
    }
}
