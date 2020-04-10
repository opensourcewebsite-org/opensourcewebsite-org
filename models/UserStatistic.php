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
    const YEAR_OF_BIRTH = 'yearOfBirth';
    const GENDER = 'gender';
    const SEXUALITY = 'sexuality';
    const CURRENCY = 'currency';
    const INTERFACE_LANGUAGE = 'interfaceLanguage';
    const LANGUAGE_AND_LEVEL = 'languageLevel';
    const CITIZENSHIP = 'citizenship';

    /**
     * @param string $type
     * @return ArrayDataProvider
     */
    public function getDataProvider(string $type)
    {
        return $this->dataProvider($type);
    }

    /**
     * @return array
     */
    protected function age()
    {
        return User::find()
            ->active()
            ->age()
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    protected function yearOfBirth()
    {
        return User::find()
            ->active()
            ->statisticYearOfBirth()
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    protected function gender()
    {
        return User::find()
            ->gender()
            ->asArray()
            ->all();
    }

    /**
     * @return ArrayDataProvider
     */
    protected function sexuality()
    {
        return User::find()
            ->sexuality()
            ->asArray()
            ->all();
    }

    /**
     * @return ArrayDataProvider
     */
    protected function currency()
    {
        return User::find()
            ->currency()
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    protected function interfaceLanguage()
    {
        return User::find()
            ->interfaceLanguage()
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    protected function languageLevel()
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
                $result[$lang][$level] = 0;
            }
            $result[$lang][$level] += $count;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function citizenship()
    {
        return User::find()
            ->citizenship()
            ->asArray()
            ->all();
    }

    /**
     * @param $type string
     * @return ArrayDataProvider
     */
    protected function dataProvider(string $type): ArrayDataProvider
    {
        $models = call_user_func([$this, $type]);

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
        return isset($models[0]) ? array_keys($models[0]) : [];
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
