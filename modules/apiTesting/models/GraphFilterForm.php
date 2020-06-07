<?php

namespace app\modules\apiTesting\models;

use kartik\form\ActiveForm;
use yii\base\Model;

class GraphFilterForm extends Model
{
    public $server_id;
    public $id;

    public function rules()
    {
        return [
            [['server_id', 'id', ], 'integer']
        ];
    }
}
