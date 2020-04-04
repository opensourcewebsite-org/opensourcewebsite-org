<?php

namespace app\models;

use app\models\queries\ContactQuery;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property int $user_id
 * @property int $link_user_id
 * @property string $name
 *
 * @property User $linkedUser
 * @property DebtRedistribution[] $debtRedistributions
 */
class Contact extends ActiveRecord
{

    const VIEW_USER = 1;
    const VIEW_VIRTUALS = 2;
    
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

    public function getLinkedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'link_user_id']);
    }

    public function getDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), [
            'from_user_id' => 'user_id',
            'to_user_id'   => 'link_user_id',
        ]);
    }

    public function getContactName()
    {
        $contactName = $this->id;
        if (!empty($this->name)) {
            $contactName = $this->name;
            if (!empty($this->linkedUser)) {
                $contactName = $this->name . ' (#' . $this->linkedUser->id . ')';
                if (!empty($this->linkedUser->username)) {
                    $contactName = $this->name . ' (@' . $this->linkedUser->username . ')';
                }
            }
        } else {
            if (!empty($this->linkedUser)) {
                $contactName = !empty($this->linkedUser->username) ? '@' . $this->linkedUser->username : '#' . $this->linkedUser->id;
            }
        }
        return $contactName;
    }

    public function canHaveDebtRedistribution(): bool
    {
        return (bool)$this->link_user_id;
    }

    public static function find()
    {
        return new ContactQuery(get_called_class());
    }

    public function transactions()
    {
        return [self::SCENARIO_DEFAULT => self::OP_DELETE];
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        $this->deleteRelations();
        parent::afterDelete();
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteRelations(): void
    {
        foreach ($this->debtRedistributions as $model) {
            if (!$model->delete()) {
                throw new Exception(VarDumper::dumpAsString([
                    'message'    => 'Fail to delete ' . $model::className(),
                    'errors'     => $model->errors,
                    'attributes' => $model->attributes,
                ]));
            }
        }
    }
}
