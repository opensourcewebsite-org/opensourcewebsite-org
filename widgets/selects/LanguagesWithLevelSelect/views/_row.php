<?php

use yii\web\View;

/**
 * @var View $this
 * @var array $languages
 * @var array $languageLevels
 * @var string $languageFieldName
 * @var string $languageLevelFieldName
 * @var string|int $selectedLanguage
 * @var string|int $selectedLanguageLevel
 */
?>

<div class="row language-row mb-2">
    <div class="col d-flex">
        <div class="col">
            <div class="form-group">
                <?= $this->render('_languages_dropdown', [
                    'languages' => $languages,
                    'languageFieldName' => $languageFieldName,
                    'selected' => $selectedLanguage,
                ]) ?>
            </div>
            <div class="help-block"></div>
        </div>
        <div class="col">
            <div class="form-group">
                <?= $this->render('_language_levels_dropdown', [
                    'languageLevels' => $languageLevels,
                    'languageLevelFieldName' => $languageLevelFieldName,
                    'selected' => $selectedLanguageLevel,
                ]) ?>
                <div class="help-block"></div>
            </div>
        </div>
    </div>
    <div class="col flex-grow-0">
        <button type="button" class="btn btn-outline-danger remove-row-btn"><i class="fa fa-minus"></i></button>
    </div>
</div>
