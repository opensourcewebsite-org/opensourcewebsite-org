<?php

namespace app\components\SupportGroupComponent;

use app\components\helpers;
use app\components\SupportGroupComponent\exceptions\LanguageException;
use app\models;

class Keeper
{

    /**
     * @param models\SupportGroup $model
     * @param array $data
     * @param array $languages
     * @throws \Exception
     */
    public function storeSupportGroup(models\SupportGroup $model, array $data, array $languages)
    {
        $requestLanguageCodes = helpers\ArrayHelper::getValue($data, 'SupportGroupLanguage', []);

        $existingLanguage = helpers\ArrayHelper::findFirst(
            $requestLanguageCodes,
            function ($languageCode) use ($languages) {
                $languageCodes = helpers\ArrayHelper::getColumn($languages, 'code');
                return in_array($languageCode, $languageCodes);
            }
        );
        if (empty($existingLanguage)) {
            $model->addError('title', 'Languages cannot be empty');
            throw new LanguageException();
        }

        if (!$model->load($data)
            || !$model->save()
        ) {
            throw new \Exception();
        }
    }

    /**
     * @param int $supportGroupId
     * @return models\SupportGroupCommand
     */
    public function createSupportGroupCommand(int $supportGroupId): models\SupportGroupCommand
    {
        $result = new models\SupportGroupCommand();
        $result->support_group_id = intval($supportGroupId);
        $result->command = '/start';
        $result->is_default = 1;
        $result->save(false);

        return $result;
    }

    /**
     * @param int $commandId
     * @param string $languageCode
     * @param string $text
     * @return models\SupportGroupCommandText
     */
    public function createSupportGroupCommandText(
        int $commandId,
        string $languageCode,
        string $text
    ): models\SupportGroupCommandText {
        $result = new models\SupportGroupCommandText();
        $result->support_group_command_id = $commandId;
        $result->language_code = $languageCode;
        $result->text = $text;
        $result->save(false);

        return $result;
    }

    /**
     * @param int $supportGroupId
     * @param array $languageCodes
     * @return models\SupportGroupLanguage[]
     */
    public function createSupportGroupLanguages(int $supportGroupId, array $languageCodes): array
    {
        $result = [];
        foreach ($languageCodes as $languageCode) {
            $model = new models\SupportGroupLanguage();
            $model->support_group_id = $supportGroupId;
            $model->language_code = $languageCode;

            if (!$model->save()) {
                continue;
            }

            $result[] = $model;
        }

        return $result;
    }

    /**
     * @param int $supportGroupId
     */
    public function removeAllSupportGroupLanguagesBySupportGroupId(int $supportGroupId)
    {
        models\SupportGroupLanguage::deleteAll(['support_group_id' => $supportGroupId]);
    }
}
