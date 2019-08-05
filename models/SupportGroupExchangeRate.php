<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_exchange_rate".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $code
 * @property string $name
 * @property string $buying_rate
 * @property string $selling_rate
 * @property int $is_default
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class SupportGroupExchangeRate extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_exchange_rate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'is_default'], 'required'],
            [['support_group_id', 'is_default'], 'integer'],
            [['buying_rate', 'selling_rate'], 'number'],
            [['code', 'name'], 'string', 'max' => 255],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'safe'],
            [['code'], 'unique', 'targetAttribute' => ['support_group_id', 'code'], 'message' => 'Code is unique per support group.'],
            ['code', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/', 'message' => 'Code is insensitive. Allowed only [a-z,A-Z,0-9,_].'],
            [['buying_rate', 'selling_rate'], 'match', 'pattern' => '/^[0-9]+(\.[0-9]{1,8})?$/', 'message' => 'Rates may contain max 8 decimals.'],
            [['is_default'], 'unique', 'targetAttribute' => ['support_group_id', 'is_default'], 'filter' => function ($query) {
                return $query->andWhere(['is_default' => true]);
            }, 'message' => 'Is Default is only 1 per support group.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_id' => 'Support Group ID',
            'code' => 'Code',
            'name' => 'Name',
            'buying_rate' => 'Buying Rate',
            'selling_rate' => 'Selling Rate',
            'is_default' => 'Is Default',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }
}
