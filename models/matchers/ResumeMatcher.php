<?php
declare(strict_types=1);
namespace app\models\matchers;

use app\models\queries\VacancyQuery;
use app\models\Resume;
use app\models\Vacancy;
use yii\db\conditions\AndCondition;

final class ResumeMatcher {

    private Resume $model;

    private ModelLinker $linker;

    private string $comparingTable;

    public function __construct(Resume $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = Vacancy::tableName();
    }

    public function match()
    {
        $this->linker->unlinkMatches();

        $vacanciesQuery = $this->prepareInitialMatchedVacanciesQuery();

        if ($this->model->min_hourly_rate) {
            $vacanciesQueryRateQuery = $this->applyRateCondition($vacanciesQuery);

            $vacanciesQueryNoRateQuery = $this->applyNoRateCondition($vacanciesQuery);

            $rateMatches = $vacanciesQueryRateQuery->all();
            $noRateMatches = $vacanciesQueryNoRateQuery->all();

            $this->linker->linkMatches($rateMatches);
            $this->linker->linkCounterMatches($rateMatches);

            $this->linker->linkCounterMatches($noRateMatches);

        } else {
            $this->linker->linkMatches($vacanciesQuery->all());
        }
    }

    private function applyRateCondition(VacancyQuery $query): VacancyQuery
    {
        return (clone $query)->andWhere(new AndCondition([
            ['IS NOT', "{$this->comparingTable}.max_hourly_rate", null],
            ['>=', "{$this->comparingTable}.max_hourly_rate", $this->model->min_hourly_rate],
            ["$this->comparingTable.currency_id" => $this->model->currency_id],
        ]));
    }

    private function applyNoRateCondition(VacancyQuery $query): VacancyQuery
    {
        return (clone $query)->andWhere(
            new AndCondition([
                ['<', "{$this->comparingTable}.max_hourly_rate", $this->model->min_hourly_rate],
                ['<>', "{$this->comparingTable}.currency_id", $this->model->currency_id],
            ]));
    }

    private function prepareInitialMatchedVacanciesQuery(): VacancyQuery
    {
        return Vacancy::find()
            ->live()
            ->matchLanguages($this->model)
            ->matchRadius($this->model)
            ->andWhere([
                '!=', "{$this->comparingTable}.user_id", $this->model->user_id,
            ]);
    }

}
