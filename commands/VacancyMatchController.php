<?php

namespace app\commands;

use app\models\matchers\VacancyMatcher;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\Vacancy;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class VacancyMatchController
 *
 * @package app\commands
 */
class VacancyMatchController extends Controller implements CronChainedInterface
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

        /** @var Vacancy $model */
        foreach ($query->all() as $model) {
            try {
                $matchesCount = (new VacancyMatcher($model))->match();

                $model->setAttributes([
                    'processed_at' => time(),
                ]);

                $model->save();

                $this->printMatchedCount($model, $matchesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: Vacancy #' . $model->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Vacancies processed: ' . $updatesCount);
        }
    }

    private function getQuery(): ActiveQuery
    {
        return Vacancy::find()
            ->where([Vacancy::tableName() . '.processed_at' => null])
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
            ->truncateTable('{{%job_vacancy_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%vacancy}}', [
                'processed_at' => null,
            ])
            ->execute();
    }
}
