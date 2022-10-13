<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\components\helpers\ArrayHelper;
use app\models\JobKeyword;
use app\models\JobResumeKeyword;
use app\models\LanguageLevel;
use app\models\matchers\interfaces\MatcherInterface;
use app\models\queries\ResumeQuery;
use app\models\Resume;
use app\models\User;
use app\models\UserLanguage;
use app\models\Vacancy;
use yii\db\Expression;

final class VacancyMatcher implements MatcherInterface
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

    public function match()
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();
        /**
         * Check languages.
         * The required language level in the vacancy corresponds to the same or higher level in the resume.
         * If there are several languages in the vacancy, then they should all be in the resume (AND condition, languages are taken from the user account).
         */
        if ($vacancyLanguages = $this->model->languages) {
            $sql = '(SELECT COUNT(*) FROM ' . UserLanguage::tableName() . ' INNER JOIN ' . LanguageLevel::tableName() . ' ON ' . UserLanguage::tableName() . '.language_level_id = ' . LanguageLevel::tableName() . '.id WHERE (';

            foreach ($vacancyLanguages as $key => $vacancyLanguage) {
                $languageLevel = $vacancyLanguage->level;

                if ($key !== 0) {
                    $sql .= ' OR ';
                }

                $sql .= '(' . UserLanguage::tableName() . '.language_id = ' . $vacancyLanguage->language_id . ' AND ' . LanguageLevel::tableName() . '.value >= ' . $languageLevel->value . ')';
            }

            $sql .= ')) = ' . count($vacancyLanguages);

            $matchesQuery->andWhere(new Expression($sql))
                ->groupBy($this->comparingTable . '.id');
        }
        // Check a gender
        if ($this->model->gender_id) {
            $matchesQuery->joinWith('user')
                ->andWhere([User::tableName() . '.gender_id' => $this->model->gender_id]);
        }
        // Check a location
        $remoteCondition = [$this->comparingTable . '.remote_on' => Resume::REMOTE_ON];

        $locationCondition = "IF(
                ({$this->comparingTable}.location_lon AND {$this->comparingTable}.location_lat AND ({$this->comparingTable}.search_radius > 0)),
                ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat}),
                    POINT({$this->comparingTable}.location_lon, {$this->comparingTable}.location_lat)),
                10000000)
            <= (1000 * {$this->comparingTable}.search_radius)";

        if ($this->model->isRemote() && $this->model->location_lat && $this->model->location_lon) {
            $matchesQuery->andWhere([
                'OR',
                $remoteCondition,
                $locationCondition,
            ]);
        } elseif ($this->model->isRemote()) {
            $matchesQuery->andWhere($remoteCondition);
        } elseif ($this->model->location_lat && $this->model->location_lon) {
            $matchesQuery->andWhere($locationCondition);
        }
        // Check keywords
        $matchesQueryKeywords = clone $matchesQuery;
        $matchesQueryNoKeywords = clone $matchesQuery;
        // TODO improve
        $matchesQueryNoKeywords = $matchesQueryNoKeywords
            ->andWhere(['not in', $this->comparingTable . '.id', JobResumeKeyword::find()->select('resume_id')]);

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

    public function prepareMainQuery(): ResumeQuery
    {
        return Resume::find()
            ->excludeUserId($this->model->user_id)
            ->live();
    }
}
