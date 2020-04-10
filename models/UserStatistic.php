<?php

namespace app\models;

use yii\data\ArrayDataProvider;

/**
 * Class UserStatistic
 *
 * Find statistic data for User model.
 * @package app\models
 */
class UserStatistic
{
    const AGE = 'age';
    const YEAR_OF_BIRTH = 'year_of_birth';
    const GENDER = 'gender';
    const SEXUALITY = 'sexuality';
    const CURRENCY = 'currency';
    const INTERFACE_LANGUAGE = 'interface_language';
    const LANGUAGE_AND_LEVEL = 'language_level';
    const CITIZENSHIP = 'citizenship';

    /**
     * @param string $type
     * @return ArrayDataProvider
     */
    public function getDataProvider(string $type)
    {
        switch ($type) {
            case self::YEAR_OF_BIRTH:
                return $this->yearOfBirth();
                break;
            case self::GENDER:
                return $this->gender();
                break;
            case self::SEXUALITY:
                return $this->sexuality();
                break;
            case self::CURRENCY:
                return $this->currency();
                break;
            case self::INTERFACE_LANGUAGE:
                return $this->interfaceLanguage();
                break;
            case self::LANGUAGE_AND_LEVEL:
                return $this->languageAndLevel();
                break;
            case self::CITIZENSHIP:
                return $this->citizenship();
                break;
            default:
                return $this->age();
                break;
        }
    }

    /**
     * @return ArrayDataProvider
     */
    protected function age()
    {
        $models = User::find()
            ->active()
            ->age()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function yearOfBirth()
    {
        $models = User::find()
            ->active()
            ->statisticYearOfBirth()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function gender()
    {
        $models = User::find()
            ->gender()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function sexuality()
    {
        $models = User::find()
            ->sexuality()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function currency()
    {
        $models = User::find()
            ->currency()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    protected function interfaceLanguage()
    {
        $models = User::find()
            ->interfaceLanguage()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function languageAndLevel()
    {
        $models = User::find()
            ->languageAndLevel()
            ->asArray()
            ->all();

        $levels = $this->langLevels();
        $levels = array_merge(['lang'], $levels);
        $keys = array_fill_keys($levels, null);

        $result = [];
        foreach ($models as $model) {
            [$lang, $level, $count] = array_values($model);

            if (! array_key_exists($lang, $result)) {
                $result[$lang] = $keys;
                $result[$lang]['lang'] = $lang;
            }

            if (! array_key_exists($level, $result[$lang])) {
                $result[$lang][$level] = $count;
            } else {
                $result[$lang][$level] += $count;
            }
        }

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ! empty($result) ? array_keys(current($result)) : [],
            ],
        ]);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function citizenship()
    {
        $models = User::find()
            ->citizenship()
            ->asArray()
            ->all();

        return $this->dataProvider($models);
    }

    /**
     * @param $models
     * @return ArrayDataProvider
     */
    protected function dataProvider(array $models): ArrayDataProvider
    {
        return new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => $this->sortAttributes($models),
            ],
        ]);
    }

    /**
     * @param array $models
     * @return array
     */
    private function sortAttributes(array $models): array
    {
        return ! empty($models) ? array_keys($models[0]) : [];
    }

    /**
     * @return array
     */
    private function langLevels(): array
    {
        $levels = LanguageLevel::find()
            ->select('description')
            ->distinct()
            ->asArray()
            ->all();

        return array_column($levels, 'description');
    }
}

