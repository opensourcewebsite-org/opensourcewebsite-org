<?php
declare(strict_types=1);

namespace app\models\search;

use app\models\Company;
use app\models\CompanyUser;
use Yii;
use yii\data\ActiveDataProvider;

class CompanyUserSearch extends Company
{
    public function rules(): array
    {
        return [];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Company::find()->joinWith('companyUser cu')->where(['cu.user_id' => Yii::$app->user->identity->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}
