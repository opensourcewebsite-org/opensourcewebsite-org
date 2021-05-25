<?php
namespace app\widgets\LanguagesWithLevelSelect;

use yii\base\Model;
use yii\bootstrap4\Widget;
use yii\widgets\ActiveForm;

class LanguagesWithLevelSelect extends Widget {

    public string $languageFieldName = 'language_id';

    public string $languageLevelFieldName = 'language_level_id';

    public string $formName = '';

    public ?Model $model = null;

    public ?ActiveForm $form = null;

    public string $label = 'Language';

    public array $languages = [];

    public array $languageLevels = [];

    private ?string $id;

    public function init()
    {
        $this->id = $this->getId();
        if ($this->model) {
            $this->formName = $this->model->formName();
        }
        if ($this->formName) {
            $this->languageFieldName = "{$this->formName}[{$this->languageFieldName}]";
            $this->languageLevelFieldName = "{$this->formName}[{$this->languageLevelFieldName}]";
        }
        $this->languageFieldName .= "[]";
        $this->languageLevelFieldName .= "[]";

    }

    public function run(): string
    {
        return $this->render('view', [
            'languageFieldName' => $this->languageFieldName,
            'languageLevelFieldName' => $this->languageLevelFieldName,
            'model' => $this->model,
            'form' => $this->form,
            'label' => $this->label,
            'id' => $this->id,
            'languages' => $this->languages,
            'languageLevels' => $this->languageLevels,
        ]);
    }

}
