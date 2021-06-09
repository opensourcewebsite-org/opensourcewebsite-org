<?php
declare(strict_types=1);

namespace app\models\matchers;

use app\components\helpers\ArrayHelper;
use app\models\LanguageLevel;
use app\models\queries\builders\UserLanguagesMatchExpressionBuilder;
use app\models\queries\builders\RadiusExpressionBuilder;
use app\models\queries\ResumeQuery;
use app\models\Resume;
use app\models\UserLanguage;
use app\models\Vacancy;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;
use yii\db\Expression;


final class VacancyMatcher
{
    private Vacancy $model;
    private ModelLinker $linker;
    private string $comparingTable;

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = Resume::tableName();
    }

    public function match(): int
    {
        $this->linker->unlinkMatches();

        $resumesQuery = $this->applyKeywordsCondition(
            $this->buildLocationAndRadiusCondition(
                $this->applyGenderCondition(
                    $this->prepareInitialMatchResumesQuery()
                )
            )
        );

        if ($this->model->max_hourly_rate) {
            $resumesQueryRateQuery = clone $resumesQuery;
            $resumesQueryNoRateQuery = clone $resumesQuery;

            $resumesQueryRateQuery->andWhere($this->buildRateAndCurrencyDirectMatchCondition());
            $resumesQueryNoRateQuery->andWhere($this->buildRateAndCurrencyNotMatchCondition());

            $rateMatches = $resumesQueryRateQuery->all();
            $rateNotMachResumes = $resumesQueryNoRateQuery->all();

            $matchesCount = count($rateMatches);

            $this->linker->linkMatches($rateMatches);
            $this->linker->linkCounterMatches($rateMatches);

            $this->linker->linkCounterMatches($rateNotMachResumes);

        } else {
            $matches = $resumesQuery->all();
            $matchesCount = count($matches);

            $this->linker->linkMatches($matches);
        }

        return $matchesCount;
    }

    public function prepareInitialMatchResumesQuery(): ResumeQuery
    {
        return Resume::find()
            ->live()
            ->andWhere($this->buildUserLanguagesMatchExpression($this->model->languagesWithLevels))
            ->andWhere([
                '!=', "{$this->comparingTable}.user_id", $this->model->user_id,
            ])
            ->groupBy("{$this->comparingTable}.id");
    }

    private function buildUserLanguagesMatchExpression(array $languages): Expression
    {
        $userLanguageTableName = UserLanguage::tableName();
        $languageLevelTable = LanguageLevel::tableName();

        if ($languages) {
            $sql = "(SELECT COUNT(*) FROM $userLanguageTableName `lang`
                INNER JOIN $languageLevelTable ON lang.language_level_id = $languageLevelTable.id WHERE (";

            foreach ($languages as $key => $vacancyLanguage) {
                $languageLevel = $vacancyLanguage->level;
                if ($key !== 0) {
                    $sql .= ' OR ';
                }
                $sql .= "lang.language_id = {$vacancyLanguage->language_id} AND $languageLevelTable.value >= $languageLevel->value";
            }
            $sql .= ")) = " . count($languages);

            return new Expression($sql);
        }

        return new Expression('');
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

    private function applyKeywordsCondition(ResumeQuery $query): ResumeQuery
    {
        $newQuery = clone $query;
        if ($keywords = $this->model->keywords) {
            $keywordIds = ArrayHelper::getColumn($keywords, 'id');
            $newQuery->joinWith('keywords kw');
            $newQuery = $newQuery->andWhere(['in', 'kw.id', $keywordIds]);
        }

        return $newQuery;
    }

    public function applyGenderCondition(ResumeQuery $query): ResumeQuery
    {
        $newQuery = clone $query;
        if ($this->model->gender_id) {
            $newQuery->andWhere(['user.gender_id' => $this->model->gender_id]);
        }

        return $newQuery;
    }

    private function buildRateAndCurrencyDirectMatchCondition(): AndCondition
    {
        return new AndCondition([
            ['IS NOT', "{$this->comparingTable}.min_hourly_rate", null],
            ['<=', "{$this->comparingTable}.min_hourly_rate", $this->model->max_hourly_rate],
            ["{$this->comparingTable}.currency_id" => $this->model->currency_id],
        ]);
    }

    private function buildRateAndCurrencyNotMatchCondition(): AndCondition
    {
        return new AndCondition([
            ['>', "{$this->comparingTable}.min_hourly_rate", $this->model->max_hourly_rate],
            ['<>', "{$this->comparingTable}.currency_id", $this->model->currency_id],
        ]);
    }
}
