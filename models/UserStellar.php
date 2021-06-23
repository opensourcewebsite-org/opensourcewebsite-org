<?php

namespace app\models;

use app\modules\bot\validators\StellarPublicKeyValidator;
use Yii;
use yii\behaviors\TimestampBehavior;

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
    public const CONFIRM_REQUEST_LIFETIME = 1200; // seconds

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'user_stellar';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
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
    public function attributeLabels(): array
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
    public function getPublicKey(): string
    {
        return $this->public_key;
    }

    /**
     * {@inheritdoc}
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed_at != null;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired(): bool
    {
        return ($this->confirmed_at == null) && ($this->created_at < (time() - self::CONFIRM_REQUEST_LIFETIME));
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeLimit(): int
    {
        return (int)self::CONFIRM_REQUEST_LIFETIME/60;
    }
}
