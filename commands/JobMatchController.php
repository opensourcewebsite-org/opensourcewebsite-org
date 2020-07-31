<?php

namespace app\commands;

use Yii;
use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\Resume;
use app\models\Vacancy;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Class JobMatchController
 *
 * @package app\commands
 */
class JobMatchController extends Controller implements CronChainedInterface
{
    use ControllerLogTrait;

    public function actionIndex()
    {
        $this->update();
    }

    protected function update()
    {
        $this->updateVacancies();
        $this->updateResumes();
    }

    protected function updateResumes()
    {
        $updatesCount = 0;

        $resumeQuery = Resume::find()
            ->where([Resume::tableName() . '.processed_at' => null])
            ->live()
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($resumeQuery->all() as $resume) {
            try {
                $resume->updateMatches();

                $resume->setAttributes([
                    'processed_at' => time(),
                ]);
                $resume->save();
                $updatesCount++;
            } catch (Exception $e) {
                echo 'ERROR: Resume #' . $resume->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Resumes updated: ' . $updatesCount);
        }
    }

    protected function updateVacancies()
    {
        $updatesCount = 0;

        $vacancyQuery = Vacancy::find()
            ->where([Vacancy::tableName() . '.processed_at' => null])
            ->live()
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC]);

        foreach ($vacancyQuery->all() as $vacancy) {
            try {
                $vacancy->updateMatches();

                $vacancy->setAttributes([
                    'processed_at' => time(),
                ]);
                $vacancy->save();
                $updatesCount++;
            } catch (Exception $e) {
                echo 'ERROR: Vacancy #' . $vacancy->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Vacancies updated: ' . $updatesCount);
        }
    }

    public function actionClearMatches()
    {
        Yii::$app->db->createCommand()
            ->truncateTable('{{%job_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update(
                '{{%resume}}',
                ['processed_at' => null])
            ->execute();

        Yii::$app->db->createCommand()
            ->update(
                '{{%vacancy}}',
                ['processed_at' => null])
            ->execute();
    }
}
