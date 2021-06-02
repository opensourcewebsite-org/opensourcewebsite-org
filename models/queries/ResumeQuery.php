<?php

namespace app\models\queries;

use app\models\Language;
use app\models\LanguageLevel;
use app\models\queries\builders\ConditionExpressionBuilderInterface;
use app\models\Resume;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use yii\db\ActiveQuery;
use yii\db\conditions\OrCondition;
use yii\db\Expression;

/**
 * Class ResumeQuery
 *
 * @package app\models\queries
 */
class ResumeQuery extends ActiveQuery
{

    public function live(): self
    {
        return $this->andWhere([Resume::tableName() . '.status' => Resume::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - Resume::LIVE_DAYS * 24 * 60 * 60]);
    }

    public function applyBuilder(ConditionExpressionBuilderInterface $builder): ResumeQuery
    {
        $ret = $builder->build();
        $new = clone $this;
        return $new->andWhere($ret);
    }
}
