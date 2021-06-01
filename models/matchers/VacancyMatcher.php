<?php
declare(strict_types=1);

namespace app\models\matchers;

use app\models\queries\builders\UserLanguagesMatchExpressionBuilder;
use app\models\queries\builders\RadiusExpressionBuilder;
use app\models\queries\ResumeQuery;
use app\models\Resume;
use app\models\Vacancy;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;


class VacancyMatcher
{

    private Vacancy $model;

    private string $comparingTable;

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
        $this->comparingTable = Resume::tableName();
    }

    public function match()
    {
        $this->unlinkMatches();

        $resumesQuery = $this->getInitialMatchResumesQuery();
        $resumesQuery = $this->buildLocationAndRadiusCondition($resumesQuery);

        $resumesQueryNoRateQuery = clone $resumesQuery;

        if ($this->model->max_hourly_rate) {
            $resumesQueryRateQuery = clone $resumesQuery;

            $resumesQueryRateQuery->andWhere($this->buildRateAndCurrencyDirectMatchCondition());
            $resumesQueryNoRateQuery->andWhere($this->buildRateAndCurrencyNotMatchCondition());

            $rateMatches = $resumesQueryRateQuery->all();
            $rateNotMachResumes = $resumesQueryNoRateQuery->all();

            $this->linkMatches($rateMatches);
            $this->linkCounterMatches($rateMatches);

            $this->linkCounterMatches($rateNotMachResumes);

        } else {
            $this->linkMatches($resumesQueryNoRateQuery->all());
        }
    }

    /**
     * @param array<Resume> $matches
     */
    public function linkMatches(array $matches)
    {
        foreach ($matches as $resume) {
            $this->model->link('matches', $resume);
        }
    }

    /**
     * @param array<Resume> $matches
     */
    public function linkCounterMatches(array $matches)
    {
        foreach ($matches as $resume) {
            $this->model->link('counterMatches', $resume);
        }
    }

    public function clearMatches()
    {
        $this->unlinkMatches();

        $this->model->processed_at = null;
        $this->model->save();
    }

    public function getInitialMatchResumesQuery(): ResumeQuery
    {
        return Resume::find()
            ->live()
            ->applyBuilder(new UserLanguagesMatchExpressionBuilder($this->model->languagesWithLevels))
            ->andWhere([
                '!=', Resume::tableName() . '.user_id', $this->model->user_id,
            ])
            ->groupBy(Resume::tableName() . '.id');
    }

    private function unlinkMatches()
    {
        $this->model->unlinkAll('matches');
        $this->model->unlinkAll('counterMatches');
    }

    private function buildLocationAndRadiusCondition(ResumeQuery $query): ResumeQuery
    {

        $newQuery = clone $query;

        $radiusExpressionBuilder = (new RadiusExpressionBuilder($this->model, $this->comparingTable));
        $remoteCondition = ["{$this->comparingTable}.remote_on" => Resume::REMOTE_ON];

        if ($this->model->location && $this->model->isRemote()) {
            $newQuery->andWhere(new OrCondition([$remoteCondition, $radiusExpressionBuilder->build()]));
        } elseif ($this->model->location) {
            $newQuery->applyBuilder($radiusExpressionBuilder);
        } elseif ($this->model->isRemote()) {
            $newQuery->andWhere($remoteCondition);
        }

        return $newQuery;
    }

    public function buildRateAndCurrencyDirectMatchCondition(): AndCondition
    {
        return new AndCondition([
            ['IS NOT', "{$this->comparingTable}.min_hourly_rate", null],
            ['<=', "{$this->comparingTable}.min_hourly_rate", $this->model->max_hourly_rate],
            ["{$this->comparingTable}.currency_id" => $this->model->currency_id],
        ]);
    }

    public function buildRateAndCurrencyNotMatchCondition(): AndCondition
    {
        return new AndCondition([
            ['>', "{$this->comparingTable}.min_hourly_rate", $this->model->max_hourly_rate],
            ['<>', "{$this->comparingTable}.currency_id", $this->model->currency_id],
        ]);
    }
}
