<?php
declare(strict_types=1);

namespace app\models\search;

use app\models\Resume;
use phpDocumentor\Reflection\Types\Static_;
use yii\data\ActiveDataProvider;

class ResumeSearch extends Resume {

    public int $status = Resume::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [Resume::STATUS_ON, Resume::STATUS_OFF]],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Resume::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
           return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status]);

        $query->orderBy(['processed_at' => SORT_DESC, 'created_at'=>SORT_DESC]);

        return $dataProvider;
    }
}
