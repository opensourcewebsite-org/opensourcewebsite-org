<?php


namespace app\commands;

use app\commands\traits\ControllerLogTrait;
use app\interfaces\CronChainedInterface;
use app\models\Resume;
use app\models\Vacancy;
use yii\console\Controller;

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

        $resumeQuery = Resume::find()->active()
            ->where([Resume::tableName() . '.processed_at' => null])
            ->joinWith('globalUser gu')
            ->orderBy(['gu.rating' => SORT_DESC])
            ->addOrderBy(['gu.created_at' => SORT_ASC]);

        foreach ($resumeQuery->all() as $resume) {
            $updatesCount++;
            $resume->updateMatches();

            $resume->setAttributes([
                'processed_at' => time(),
            ]);
            $resume->save();
        }

        if ($updatesCount) {
            $this->output('Resumes updated: ' . $updatesCount);
        }
    }

    protected function updateVacancies()
    {
        $updatesCount = 0;

        $vacancyQuery = Vacancy::find()->active()
            ->where([Vacancy::tableName() . '.processed_at' => null])
            ->joinWith('globalUser gu')
            ->orderBy(['gu.rating' => SORT_DESC])
            ->addOrderBy(['gu.created_at' => SORT_ASC]);

        foreach ($vacancyQuery->all() as $vacancy) {
            $updatesCount++;
            $vacancy->updateMatches();

            $vacancy->setAttributes([
                'processed_at' => time(),
            ]);
            $vacancy->save();
        }

        if ($updatesCount) {
            $this->output('Vacancies updated: ' . $updatesCount);
        }
    }
}
