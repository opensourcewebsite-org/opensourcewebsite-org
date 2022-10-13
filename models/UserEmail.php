<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_email".
 *
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property int|null $confirmed_at
 */
class UserEmail extends ActiveRecord
{
    public const CONFIRM_REQUEST_LIFETIME = 24 * 60 * 60; // seconds

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user_email}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'email'], 'required'],
            [['user_id', 'confirmed_at'], 'integer'],
            [['email'], 'string', 'max' => 255],
            ['email', 'email'],
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
            'email' => 'Email',
            'confirmed_at' => 'Confirmed At',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, [ 'id' => 'user_id' ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
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
    public function confirm()
    {
        // reset all other confirmations
        self::updateAll(
            [
                'confirmed_at' => null,
            ],
            [
                'email' => $this->email,
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
        if (!$insert && $this->confirmed_at && $this->isAttributeChanged('email')) {
            $this->confirmed_at = null;
        }

        return parent::beforeSave($insert);
    }
}
