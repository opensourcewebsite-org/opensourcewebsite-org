<?php
declare(strict_types=1);

namespace app\models\search;

use app\models\Vacancy;
use app\modules\bot\validators\LocationLatValidator;
use app\modules\bot\validators\LocationLonValidator;
use Yii;
use yii\data\ActiveDataProvider;

class VacancySearch extends Vacancy {

    public function rules(): array
    {
        return [
            [
                [
                    'user_id',
                    'company_id',
                    'currency_id',
                    'status',
                    'gender_id',
                    'created_at',
                    'processed_at',
                ],
                'integer',
            ],
            [
                'max_hourly_rate',
                'string'
            ],
            [
                [
                    'name',
                ],
                'string',
                'max' => 255,
            ],
            [
                [
                    'requirements',
                    'conditions',
                    'responsibilities',
                ],
                'string',
                'max' => 10000,
            ],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Vacancy::find()->where(['user_id' => Yii::$app->user->getIdentity()->getId()]);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);
            return $dataProvider;
        }

        $query->andFilterWhere([]);
    }
}
