<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\components\helpers\ArrayHelper;
use app\models\JobKeyword;
use app\models\JobVacancyKeyword;
use app\models\LanguageLevel;
use app\models\matchers\interfaces\MatcherInterface;
use app\models\queries\VacancyQuery;
use app\models\Resume;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use yii\db\Expression;

final class ResumeMatcher implements MatcherInterface
{
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
        $matchesQuery = $this->prepareMainQuery();
        /**
         * Check languages.
         * The required language level in the vacancy corresponds to the same or higher level in the resume.
         * If there are several languages in the vacancy, then they should all be in the resume (AND condition, languages are taken from the user account).
         */
        if ($userLanguages = $this->model->languages) {
            $sql = '(SELECT COUNT(*) FROM ' . VacancyLanguage::tableName() . ' INNER JOIN ' . LanguageLevel::tableName() . ' ON ' . VacancyLanguage::tableName() . '.language_level_id = ' . LanguageLevel::tableName() . '.id WHERE ' . Vacancy::tableName() . '.id = ' . VacancyLanguage::tableName() . '.vacancy_id AND (';

            foreach ($userLanguages as $key => $userLanguage) {
                $languageLevel = $userLanguage->level;

                if ($key) {
                    $sql .= ' OR ';
                }

                $sql .= VacancyLanguage::tableName() . '.language_id = ' . $userLanguage->language_id . ' AND ' . LanguageLevel::tableName() . '.value <= ' . $languageLevel->value;
            }

            $sql .= ')) = (SELECT COUNT(*) FROM ' . VacancyLanguage::tableName() . ' WHERE ' . Vacancy::tableName() . '.id = ' . VacancyLanguage::tableName() . '.vacancy_id)';

            $matchesQuery->andWhere(new Expression($sql));
        }
        // TODO Check a gender
        // Check a location
        $remoteCondition = [$this->comparingTable . '.remote_on' => Vacancy::REMOTE_ON];

        $locationCondition = "IF(
                ({$this->comparingTable}.location_lon AND {$this->comparingTable}.location_lat),
                ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat} ),
                    POINT({$this->comparingTable}.location_lon, {$this->comparingTable}.location_lat)),
                10000000)
            <= (1000 * {$this->model->search_radius})";

        if ($this->model->isRemote() && $this->model->isOffline()) {
            $matchesQuery->andWhere([
                'OR',
                $remoteCondition,
                $locationCondition,
            ]);
        } elseif ($this->model->isRemote()) {
            $matchesQuery->andWhere($remoteCondition);
        } elseif ($this->model->isOffline()) {
            $matchesQuery->andWhere($locationCondition);
        }
        // Check keywords
        $matchesQueryKeywords = clone $matchesQuery;
        $matchesQueryNoKeywords = clone $matchesQuery;
        // TODO improve
        $matchesQueryNoKeywords = $matchesQueryNoKeywords
            ->andWhere(['not in', $this->comparingTable . '.id', JobVacancyKeyword::find()->select('vacancy_id')]);

        if ($keywords = $this->model->keywords) {
            $keywordsIds = ArrayHelper::getColumn($keywords, 'id');
            $matchesQueryKeywords->joinWith('keywords');
            $matchesQueryKeywords->andWhere(['in', JobKeyword::tableName() . '.id', $keywordsIds]);

            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matches = $keywordsMatches;
            // TODO fix to only unique values
            $counterMatches = ArrayHelper::merge($keywordsMatches, $noKeywordsMatches);
        } else {
            $matches = $matchesQuery->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $counterMatches = $noKeywordsMatches;
        }

        $matchesCount = count($matches);

        $this->linker->linkMatches($matches);
        $this->linker->linkCounterMatches($counterMatches);

        return $matchesCount;
    }

    private function prepareMainQuery(): VacancyQuery
    {
        return Vacancy::find()
            ->excludeUserId($this->model->user_id)
            ->live();
    }
}
