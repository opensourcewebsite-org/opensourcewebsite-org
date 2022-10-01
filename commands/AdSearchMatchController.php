<?php

namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\AdSearch;
use app\models\AdSearchMatch;
use app\models\matchers\AdSearchMatcher;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdSearchMatchController
 *
 * @package app\commands
 */
class AdSearchMatchController extends Controller implements CronChainedInterface
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

        /** @var AdSearch $model */
        foreach ($query->all() as $model) {
            try {
                $matchesCount = (new AdSearchMatcher($model))->match();

                $model->setAttributes([
                    'processed_at' => time(),
                ]);

                $model->save();

                $this->printMatchedCount($model, $matchesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: AdSearch #' . $model->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Searches processed: ' . $updatesCount);
        }
    }

    private function getQuery(): ActiveQuery
    {
        return AdSearch::find()
            ->where([AdSearch::tableName() . '.processed_at' => null])
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
            ->truncateTable(AdSearchMatch::tableName())
            ->execute();

        Yii::$app->db->createCommand()
            ->update(AdSearch::tableName(), [
                'processed_at' => null,
            ])
            ->execute();
    }
}
