<?php
declare(strict_types=1);

namespace app\models\search;

use app\models\AdSearch;
use Yii;
use yii\data\ActiveDataProvider;

class AdSearchSearch extends AdSearch
{

    public function rules(): array
    {
        return [
            [
                ['id', 'currency_id'],
                'integer']
            ,
            ['title', 'string'],
            ['max_price', 'double'],

        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = AdSearch::find()->where(['user_id' => Yii::$app->user->getIdentity()->getId()]);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['max_price' => $this->max_price])
            ->andFilterWhere(['currency_id' => $this->currency_id]);

        return $dataProvider;
    }
}
