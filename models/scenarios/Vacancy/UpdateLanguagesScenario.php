<?php

declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use app\components\helpers\ArrayHelper;
use app\models\forms\LanguageWithLevelsForm;
use app\models\Vacancy;
use app\models\VacancyLanguage;

// TODO remove old code
class UpdateLanguagesScenario
{
    private LanguageWithLevelsForm $form;
    private Vacancy $model;

    public function __construct(Vacancy $model, LanguageWithLevelsForm $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    public function run()
    {
        $currentLanguages = $this->model->getLanguages()->asArray()->all();
        $currentLanguagesMapped = ArrayHelper::map($currentLanguages, 'language_id', 'language_level_id');
        $newLanguagesMapped = $this->prepareLangAndLevel();
        $toDelete = array_diff_key($currentLanguagesMapped, $newLanguagesMapped);
        $toAdd = array_diff_key($newLanguagesMapped, $currentLanguagesMapped);
        $sameIds = array_intersect_key($currentLanguagesMapped, $newLanguagesMapped);
        $toChange = [];

        foreach ($sameIds as $id => $langLevel) {
            if ($langLevel !== $newLanguagesMapped[$id]) {
                $toChange[$id] = $newLanguagesMapped[$id];
            }
        }

        if ($toAdd || $toDelete || $toChange) {
            $this->model->trigger(Vacancy::EVENT_LANGUAGES_UPDATED);
        }

        if ($toDelete) {
            VacancyLanguage::deleteAll(['vacancy_id' => $this->model->id, 'language_id' => array_keys($toDelete)]);
        }

        foreach ($toAdd as $langId => $langLevelId) {
            (new VacancyLanguage([
                'vacancy_id' => $this->model->id,
                'language_id' => $langId,
                'language_level_id' => $langLevelId
            ]))->save();
        }

        foreach ($toChange as $langId => $langLevelId) {
            /** @var VacancyLanguage $vacancyLanguage */
            $vacancyLanguage = VacancyLanguage::find()
                ->where(['vacancy_id' => $this->model->id])
                ->andWhere(['language_id' => $langId])
                ->one();
            $vacancyLanguage->language_level_id = $langLevelId;
            $vacancyLanguage->save();
        }
    }

    public function prepareLangAndLevel(): array
    {
        $ret = [];
        foreach ($this->form->language_id as $key => $langId) {
            if (is_numeric($langId) && isset($this->form->language_level_id[$key])) {
                $ret[$langId] = $this->form->language_level_id[$key];
            }
        }

        return $ret;
    }
}
