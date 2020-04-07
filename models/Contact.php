<?php

namespace app\models;

use app\models\queries\ContactQuery;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property int $user_id
 * @property int $link_user_id
 * @property string $name
 * @property int $debt_redistribution_priority "1" - the highest. "0" - no priority.
 *
 * @property User $linkedUser
 * @property DebtRedistribution[] $debtRedistributions
 */
class Contact extends ActiveRecord
{
    public const DEBT_REDISTRIBUTION_PRIORITY_NO = null;

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
            ['name', 'required',
                'when' => static function (self $model) {
                    return empty($model->userIdOrName);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-useridorname').val() == '';
                }",
            ],
            ['debt_redistribution_priority', 'integer', 'min' => 0, 'max' => 255],
            ['debt_redistribution_priority', 'filter', 'filter' => static function ($v) { return ((int)$v) ?: null; }],
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

    public function attributeHints()
    {
        return [
            'debt_redistribution_priority' => Html::ul([
                '"0" - no priority.',
                '"1" - the highest.',
                "Note: it has no affect, if field \"{$this->getAttributeLabel('userIdOrName')}\" is empty",
            ]),
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
        return !$this->isVirtual();
    }

    public function isVirtual(): bool
    {
        return !$this->link_user_id;
    }

    public function isPriorityEmpty(): bool
    {
        return $this->debt_redistribution_priority == self::DEBT_REDISTRIBUTION_PRIORITY_NO;
    }

    public static function find()
    {
        return new ContactQuery(get_called_class());
    }

    public function transactions()
    {
        return [self::SCENARIO_DEFAULT => self::OP_DELETE | self::OP_UPDATE];
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        $this->deleteDebtRedistributions($this->debtRedistributions);
        parent::afterDelete();
    }

    /**
     * @param bool $insert
     *
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->deleteOldUserSettings();

        return true;
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteOldUserSettings(): void
    {
        $oldId = $this->getOldAttribute('link_user_id');
        if ($oldId && $this->isAttributeChanged('link_user_id')) {
            $models = DebtRedistribution::find()
                ->fromUser($this->user_id)
                ->toUser($oldId)
                ->all();
            $this->deleteDebtRedistributions($models);
        }
    }

    /**
     * @param DebtRedistribution[] $models
     *
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function deleteDebtRedistributions($models): void
    {
        foreach ($models as $model) {
            if (!$model->delete()) {
                throw new Exception(VarDumper::dumpAsString([
                    'message'    => 'Fail to delete ' . $model::className(),
                    'errors'     => $model->errors,
                    'attributes' => $model->attributes,
                ]));
            }
        }
    }

    public function getUserIdOrName()
    {
        return empty($this->linkedUser->username) ? $this->link_user_id : $this->linkedUser->username;
    }
}
