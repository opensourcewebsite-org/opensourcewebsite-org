<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Moqup model
 *
 *
 */
class Css extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'css';
    }

}
