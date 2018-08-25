<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Moqup model
 *
 *
 */
class Moqup extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'moqup';
    }

    public function rules()
    {
        return [
            // email and password are both required
            [['title', 'html'], 'required']
        ];
    }

}
