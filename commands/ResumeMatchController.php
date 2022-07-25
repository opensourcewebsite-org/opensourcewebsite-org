<?php

namespace app\commands;

use app\models\matchers\ResumeMatcher;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\Resume;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * Class ResumeMatchController
 *
 * @package app\commands
 */
class ResumeMatchController extends Controller implements CronChainedInterface
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

        /** @var Resume $model */
        foreach ($query->all() as $model) {
            try {
                $matchesCount = (new ResumeMatcher($model))->match();

                $model->setAttributes([
                    'processed_at' => time(),
                ]);

                $model->save();

                $this->printMatchedCount($model, $matchesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: Resume #' . $model->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Resumes processed: ' . $updatesCount);
        }
    }

    private function getQuery(): ActiveQuery
    {
        return Resume::find()
            ->where([Resume::tableName() . '.processed_at' => null])
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
            ->truncateTable('{{%job_resume_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%resume}}', [
                'processed_at' => null,
            ])
            ->execute();
    }
}
