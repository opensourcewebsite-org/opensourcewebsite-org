<?php

namespace app\models;

use app\modules\bot\validators\StellarPublicKeyValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_stellar".
 *
 * @property int $id
 * @property int $user_id
 * @property string $public_key
 * @property int $created_at
 * @property int|null $confirmed_at
 */
class UserStellar extends ActiveRecord
{
    public const CONFIRM_REQUEST_LIFETIME = 20 * 60; // seconds

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_stellar}}';
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
            ['public_key', StellarPublicKeyValidator::class],
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
            'public_key' => Yii::t('app', 'Public Key'),
            'created_at' => Yii::t('app', 'Created At'),
            'confirmed_at' => Yii::t('app', 'Confirmed At'),
        ];
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

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, [ 'id' => 'user_id' ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicKey(): string
    {
        return $this->public_key ?? '';
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
        return (int)(self::CONFIRM_REQUEST_LIFETIME / 60);
    }

    /**
     * {@inheritdoc}
     */
    public function confirm()
    {
        // reset all other confirmations
        self::updateAll(
            [
                'confirmed_at' => null,
            ],
            [
                'public_key' => $this->public_key,
            ]
        );

        $this->confirmed_at = time();

        if ($this->save()) {
            return true;
        }

        return false;
    }

    public function beforeSave($insert)
    {
        if (!$insert && $this->confirmed_at && $this->isAttributeChanged('public_key')) {
            $this->confirmed_at = null;
        }

        return parent::beforeSave($insert);
    }
}
