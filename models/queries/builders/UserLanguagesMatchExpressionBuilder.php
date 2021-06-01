<?php
declare(strict_types=1);

namespace app\models\queries\builders;

use app\models\LanguageLevel;
use app\models\UserLanguage;
use app\models\VacancyLanguage;
use yii\db\ActiveQuery;
use yii\db\Expression;

class UserLanguagesMatchExpressionBuilder implements ConditionExpressionBuilderInterface {


    /**
     * @var array<array<VacancyLanguage>>
     */
    private array $languages;

    public function __construct(array $languages)
    {
        $this->languages = $languages;
    }

    public function build(): Expression
    {
        $userLanguageTableName = UserLanguage::tableName();
        $languageLevelTable = LanguageLevel::tableName();

        if ($this->languages) {
            $sql = "(SELECT COUNT(*) FROM $userLanguageTableName `lang`
                INNER JOIN $languageLevelTable ON lang.language_level_id = $languageLevelTable.id WHERE (";

            foreach ($this->languages as $key => $vacancyLanguage) {
                $languageLevel = $vacancyLanguage->level;
                if ($key !== 0) {
                    $sql .= ' OR ';
                }
                $sql .= "lang.language_id = {$vacancyLanguage->language_id} AND $languageLevelTable.value >= $languageLevel->value";
            }
            $sql .= ")) = " . count($this->languages);

            return new Expression($sql);
        }

        return new Expression('');
    }
}
