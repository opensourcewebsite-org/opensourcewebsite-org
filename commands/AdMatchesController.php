<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\WikinewsLanguage;
use app\models\WikinewsPage;
use yii\console\Controller;
use yii\httpclient\Client;
use app\modules\bot\models\AdOrder;
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

            $adOrderUpdated = $this->updateAdOrder();
            $adSearchUpdated = $this->updateAdSearch();

            if (!$adOrderUpdated && !$adSearchUpdated) {
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

    protected function updateAdOrder()
    {
        $this->output('Running updateAdOrder...');

        $minEditedAt = AdOrder::find()->min('edited_at');

        if ($minEditedAt === null) {
            return false;
        }

        $adOrder = AdOrder::find()->where([
            'edited_at' => $minEditedAt,
        ])->one();

        if (!isset($adOrder)) {
            return false;
        }

        $adOrder->updateMatches();

        $adOrder->setAttributes([
            'edited_at' => null,
        ]);
        $adOrder->save();

        return true;
    }
}
