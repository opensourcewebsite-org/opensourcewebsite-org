<?php

namespace app\models\search;

use app\models\SupportGroupLanguage;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Class CronJobSearch
 *
 * @package app\models\search
 */
class SupportGroupLanguageSearch extends SupportGroupLanguage
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {

        $query = self::find()->with('languageCode');

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'support_group_id' => $this->support_group_id,
        ]);

        return $dataProvider;
    }
}
