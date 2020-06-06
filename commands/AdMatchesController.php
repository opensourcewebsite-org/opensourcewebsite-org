<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\console\Controller;
use yii\httpclient\Client;
use app\modules\bot\models\AdsPost;
use app\modules\bot\models\AdsPostSearch;

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

            $adsPostUpdated = $this->updateAdsPost();
            $adsPostSearchUpdated = $this->updateAdsPostSearch();

            if (!$adsPostUpdated && !$adsPostSearchUpdated) {
                break;
            }
        }
    }

    protected function updateAdsPostSearch()
    {
        $this->output('Running updateAdsPostSearch...');

        $minEditedAt = AdsPostSearch::find()->min('edited_at');

        if ($minEditedAt === null) {
            return false;
        }

        $adsPostSearch = AdsPostSearch::find()->where([
            'edited_at' => $minEditedAt,
        ])->one();

        if (!isset($adsPostSearch)) {
            return false;
        }

        $adsPostSearch->updateMatches();

        $adsPostSearch->setAttributes([
            'edited_at' => null,
        ]);
        $adsPostSearch->save();

        return true;
    }

    protected function updateAdsPost()
    {
        $this->output('Running updateAdsPost...');

        $minEditedAt = AdsPost::find()->min('edited_at');

        if ($minEditedAt === null) {
            return false;
        }

        $adsPost = AdsPost::find()->where([
            'edited_at' => $minEditedAt,
        ])->one();

        if (!isset($adsPost)) {
            return false;
        }

        $adsPost->updateMatches();

        $adsPost->setAttributes([
            'edited_at' => null,
        ]);
        $adsPost->save();

        return true;
    }
}
