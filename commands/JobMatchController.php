<?php

namespace app\commands;

use app\models\matchers\ResumeMatcher;
use app\models\matchers\VacancyMatcher;
use Yii;
use yii\console\Controller;
use app\interfaces\CronChainedInterface;
use app\commands\traits\ControllerLogTrait;
use app\models\Resume;
use app\models\Vacancy;
use yii\db\ActiveRecord;

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

        foreach ($this->getResumes() as $resume) {
            try {
                $matchedVacanciesCount = (new ResumeMatcher($resume))->match();

                $resume->setAttributes([
                    'processed_at' => time(),
                ]);
                $resume->save();

                $this->printMatchedCount($resume, $matchedVacanciesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: Resume #' . $resume->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Resumes processed: ' . $updatesCount);
        }
    }

    protected function updateVacancies()
    {
        $updatesCount = 0;

        foreach ($this->getVacancies() as $vacancy) {
            try {
                $matchedResumesCount = (new VacancyMatcher($vacancy))->match();

                $vacancy->setAttributes([
                    'processed_at' => time(),
                ]);
                $vacancy->save();

                $this->printMatchedCount($vacancy, $matchedResumesCount);

                $updatesCount++;
            } catch (\Exception $e) {
                echo 'ERROR: Vacancy #' . $vacancy->id . ': ' . $e->getMessage() . "\n";
            }
        }

        if ($updatesCount) {
            $this->output('Vacancies processed: ' . $updatesCount);
        }
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
            ->truncateTable('{{%job_vacancy_match}}')
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%resume}}', [
                'processed_at' => null,
            ])
            ->execute();

        Yii::$app->db->createCommand()
            ->update('{{%vacancy}}', [
                'processed_at' => null,
            ])
            ->execute();
    }

    /**
     * @return array<Resume>
     */
    private function getResumes(): array
    {
        return Resume::find()
            ->where([Resume::tableName() . '.processed_at' => null])
            ->live()
            ->orderBy([
                'user.rating' => SORT_DESC,
                'user.created_at' => SORT_ASC,
            ]);
            ->all();
    }

    /**
     * @return array<Vacancy>
     */
    private function getVacancies(): array
    {
        return Vacancy::find()
            ->where([Vacancy::tableName() . '.processed_at' => null])
            ->live()
            ->orderBy(['user.rating' => SORT_DESC])
            ->addOrderBy(['user.created_at' => SORT_ASC])
            ->all();
    }
}
