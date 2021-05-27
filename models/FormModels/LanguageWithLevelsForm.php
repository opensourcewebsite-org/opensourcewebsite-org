<?php
declare(strict_types=1);

namespace app\models\FormModels;

use app\models\VacancyLanguage;
use yii\base\Model;

class LanguageWithLevelsForm extends Model {

    public array $language_id = [];

    public array $language_level_id = [];

    public bool $required = false;

    public function rules(): array
    {
        $rules =  [
            [
                ['language_id','language_level_id'], 'each', 'rule' => ['integer'],
            ]
        ];
        if ($this->required) {
            $rules[] = [['language_id', 'language_level_id'], 'required'];
        }
        return $rules;
    }

    /**
     * @param VacancyLanguage[] $languages
     */
    public function setSelectedLanguages(array $languages)
    {
        foreach ($languages as $language) {
            $this->addLanguageAndLevelIds((int)$language->language_id, (int)$language->language_level_id);
        }
    }

    public function addLanguageAndLevelIds(int $languageId, int $languageLevelId)
    {
        $this->language_id[] = $languageId;
        $this->language_level_id[] = $languageLevelId;
    }
}
