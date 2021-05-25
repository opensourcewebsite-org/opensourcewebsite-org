<?php
declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use app\components\helpers\ArrayHelper;
use app\models\FormModels\LanguageWithLevelsForm;
use app\models\Vacancy;
use app\models\VacancyLanguage;

class UpdateLanguagesScenario {

    private LanguageWithLevelsForm $form;

    private Vacancy $model;

    public function __construct(Vacancy $model, LanguageWithLevelsForm $form)
    {
        $this->model = $model;
        $this->form = $form;
    }

    public function run()
    {
        $currentLanguages = $this->model->getLanguagesWithLevels()->asArray()->all();
        $currentLanguagesMapped = ArrayHelper::map($currentLanguages, 'language_id', 'language_level_id');
        $newLanguagesMapped = array_combine($this->form->language_id, $this->form->language_level_id);
        $toDelete = array_diff($currentLanguagesMapped, $newLanguagesMapped);
        $toAdd = array_diff($newLanguagesMapped, $currentLanguagesMapped);
        $sameIds = array_intersect_key($currentLanguagesMapped, $newLanguagesMapped);
        $toChange = [];
        foreach ($sameIds as $id => $langLevel) {
            if ($langLevel !== $newLanguagesMapped[$id]) {
                $toChange[$id] = $newLanguagesMapped[$id];
            }
        }
        if ($toDelete) {
            VacancyLanguage::deleteAll(['vacancy_id' => $this->model->id, ['in', 'language_id', array_keys($toDelete)]]);
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
}
