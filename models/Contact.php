<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property int $user_id
 * @property int $link_user_id
 * @property string $name
 */
class Contact extends ActiveRecord
{

    public $userIdOrName;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['userIdOrName', 'string'],
            ['userIdOrName', 'required'],
            ['userIdOrName', 'validateUserExistence'],
            [['user_id', 'link_user_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['name', 'required', 'when' => function ($model) {
                return empty($model->userIdOrName);
            }, 'whenClient' => "function (attribute, value) {
                return $('#contact-useridorname').val() == '';
            }"],
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
            'link_user_id' => 'Link User ID',
            'name' => 'Name',
            'userIdOrName' => 'User ID / Username',
        ];
    }

    /**
     * Validates the user existence.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validateUserExistence($attribute)
    {
        $user = User::find()
            ->andWhere([
                'OR',
                    ['id' => $this->userIdOrName],
                    ['username' => $this->userIdOrName]
            ])
            ->one();
        if (empty($user)) {
            return $this->addError($attribute, "User ID / Username doesn't exists.");
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getContactName()
    {
        if (!empty($this->name)) {
            if (!empty($this->user->username)) {
                $contactName = $this->name . ' (' . $this->user->username . ')';
            } else {
                $contactName = $this->name . ' (#' . $this->user->id . ')';
            }
        } else {
            if (!empty($this->user)) {
                $contactName = !empty($this->user->username) ? $this->user->username : '#' . $this->user->id;
            }
        }
        return $contactName;
    }
}
