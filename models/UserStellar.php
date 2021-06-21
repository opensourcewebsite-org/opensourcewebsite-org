<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use app\modules\bot\validators\StellarPublicKeyValidator;

/**
 * This is the model class for table "user_stellar".
 *
 * @property int $id
 * @property int $user_id
 * @property string $public_key
 * @property int $created_at
 * @property int|null $confirmed_at
 */
class UserStellar extends \yii\db\ActiveRecord
{
    public const CONFIRM_REQUEST_LIFETIME = 600; // seconds

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_stellar';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=> TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'public_key'], 'required'],
            [['user_id', 'created_at', 'confirmed_at'], 'integer'],
            [
                [
                    'public_key',
                ],
                'string',
                'length' => 56,
            ],
            [
                'public_key',
                StellarPublicKeyValidator::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'public_key' => 'Public Key',
            'created_at' => 'Created At',
            'confirmed_at' => 'Confirmed At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * {@inheritdoc}
     */
    public function isConfirmed()
    {
        if ($this->confirmed_at != null) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        if (($this->confirmed_at == null) && ($this->created_at < (time() - self::CONFIRM_REQUEST_LIFETIME))) {
            return true;
        }

        return false;
    }
}
