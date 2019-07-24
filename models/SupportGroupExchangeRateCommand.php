<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_exchange_rate_command".
 *
 * @property int $id
 * @property int $support_group_exchange_rate_id
 * @property string $command
 * @property int $type
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class SupportGroupExchangeRateCommand extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_exchange_rate_command';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_exchange_rate_id', 'command', 'type'], 'required'],
            [['support_group_exchange_rate_id', 'type'], 'integer'],
            [['command'], 'string', 'max' => 255],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_exchange_rate_id' => 'Support Group Exchange Rate ID',
            'command' => 'Command',
            'type' => 'Type',
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
