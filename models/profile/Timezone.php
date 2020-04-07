<?php

namespace app\models\profile;

use yii\base\Model;

class Timezone extends Model
{

    public $timezone;

    public function rules()
    {
        return [
            ['timezone', 'required'],
        ];
    }
}
