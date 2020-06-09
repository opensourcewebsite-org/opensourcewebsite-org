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
        while (true) {
            $this->output('Running update...');

            $adOfferUpdated = $this->updateAdOffer();
            $adSearchUpdated = $this->updateAdSearch();

            if (!$adOfferUpdated && !$adSearchUpdated) {
                break;
            }
        }
    }

    protected function updateAdSearch()
    {
        $this->output('Running updateAdSearch...');

        $minEditedAt = AdSearch::find()->min('edited_at');

        if ($minEditedAt === null) {
            return false;
        }

        $adSearch = AdSearch::find()->where([
            'edited_at' => $minEditedAt,
        ])->one();

        if (!isset($adSearch)) {
            return false;
        }

        $adSearch->updateMatches();

        $adSearch->setAttributes([
            'edited_at' => null,
        ]);
        $adSearch->save();

        return true;
    }

    protected function updateAdOffer()
    {
        $this->output('Running updateAdOffer...');

        $minEditedAt = AdOffer::find()->min('edited_at');

        if ($minEditedAt === null) {
            return false;
        }

        $adOffer = AdOffer::find()->where([
            'edited_at' => $minEditedAt,
        ])->one();

        if (!isset($adOffer)) {
            return false;
        }

        $adOffer->updateMatches();

        $adOffer->setAttributes([
            'edited_at' => null,
        ]);
        $adOffer->save();

        return true;
    }
}
