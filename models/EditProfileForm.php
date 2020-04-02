<?php

namespace app\models;

use yii\base\Model;
use yii\web\ServerErrorHttpException;

class  EditProfileForm extends Model
{

    public $field;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['field', 'required', 'message' => 'Fill the field!'],
        ];
    }

}
