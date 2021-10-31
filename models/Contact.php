<?php

namespace app\models;

use Yii;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\interfaces\UserRelation\ByOwnerTrait;
use app\models\queries\ContactQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\traits\SelectForUpdateTrait;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\components\helpers\Html;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property int $user_id
 * @property int $link_user_id
 * @property string $name
 * @property int $debt_redistribution_priority "1" - the highest. "0" - no priority. Priority of "Creditor Reliability"
 * @property int $vote_delegation_priority
 * @property int $is_real
 * @property int $relation
 *
 * @property User $ownerUser
 * @property User $linkedUser
 * @property DebtRedistribution[] $debtRedistributions
 * @property DebtRedistribution $debtRedistributionByDebtorCustom
 * @property Contact[] $chainMembers
 * @property Contact $chainMemberParent   you should not use this relation for SQL query. It's only purpose -
 *                                        to be used as `inverseOf`
 *                                        for relation {@see Contact::getChainMembers()}
 *                                        in {@see Reduction::findDebtReceiverCandidatesRedistributeInto()}
 */
class Contact extends ActiveRecord implements ByOwnerInterface
{
    use ByOwnerTrait;
    use SelectForUpdateTrait;

    public const DEBT_REDISTRIBUTION_PRIORITY_DENY = 0;
    public const DEBT_REDISTRIBUTION_PRIORITY_MAX = 255;

    public const RELATION_LABELS = [
        0 => 'Neutral',
        1 => 'Positive',
        2 => 'Negative',
    ];

    public const IS_REAL_LABELS = [
        0 => 'Virtual',
        1 => 'Real',
    ];

    public const IS_BASIC_INCOME_CANDIDATE_LABELS = [
        0 => 'No',
        1 => 'Yes',
    ];

    public $userIdOrName;

    public $groupIds = [];

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
            [['user_id'], 'required'],
            [['user_id', 'link_user_id', 'relation'], 'integer'],
            [['relation'], 'integer', 'min' => 0, 'max' => 2],
            ['userIdOrName', 'string'],
            ['userIdOrName', 'trim'],
            [['userIdOrName'], 'required',
                'when' => static function (self $model) {
                    return empty($model->name);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-name').val() == '';
                }",
                'message' => $this->getAttributeLabel('userIdOrName') . ' cannot be blank if ' . $this->getAttributeLabel('name') . ' is empty.',
            ],
            ['userIdOrName', 'validateUserExists'],
            ['link_user_id', 'validateLinkUserAndOwnerNotSame'],
            ['link_user_id', 'validateLinkUsedIdUnique'],
            ['name', 'required',
                'when' => static function (self $model) {
                    return empty($model->userIdOrName);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-useridorname').val() == '';
                }",
                'message' => $this->getAttributeLabel('name') . ' cannot be blank if ' . $this->getAttributeLabel('userIdOrName') . ' is empty.',
            ],
            [['name'], 'string', 'max' => 255],
            [['name'], 'default'],
            [
                [
                    'vote_delegation_priority',
                    'debt_redistribution_priority',
                ],
                'integer',
                'min' => 0,
                'max' => 255,
            ],
            [
                [
                    'vote_delegation_priority',
                    'debt_redistribution_priority',
                ],
                'filter',
                'filter' => static function ($v) {
                    return ((int)$v) ?: 0;
                },
            ],
            ['is_real', 'boolean'],
            ['is_basic_income_candidate', 'boolean'],
            [['is_real', 'relation', 'is_basic_income_candidate'], 'default', 'value' => 0],
            [
                'groupIds', 'filter', 'filter' => function ($val) {
                    if ($val === '') {
                        return [];
                    }
                    return $val;
                }
            ],
            [
                'groupIds', 'each', 'rule' => ['integer'],
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
            'link_user_id' => 'Link User ID',
            'name' => Yii::t('user', 'Name'),
            'userIdOrName' => 'User ID / Username',
            'is_real' => Yii::t('app', 'Personal identification'),
            'relation' => Yii::t('app', 'Personal relation'),
            'is_basic_income_candidate' => Yii::t('app', 'Basic income candidate'),
            'vote_delegation_priority' => Yii::t('app', 'Vote delegation priority'),
            'debt_redistribution_priority' => Yii::t('app', 'Debt transfer priority'),
            'groupIds' => Yii::t('app', 'Groups'),
        ];
    }

    public function attributeHints()
    {
        return [
            'debt_redistribution_priority' => Html::ul([
                'It\'s priority of debtor\'s reliability. Debts will not redistributed to contacts with lower priority.',
                '1 - the highest. 255 - the lowest.',
                'It has no affect, if ' . $this->getAttributeLabel('userIdOrName') . ' is empty.',
            ]),
            'vote_delegation_priority' => Html::ul([
                '1 - the highest. 255 - the lowest.',
                'It has no affect, if field ' . $this->getAttributeLabel('userIdOrName') . ' is empty.',
            ]),
            'userIdOrName' => Yii::t('app', 'To associate this contact with another user') . '.',
            'is_real' => Yii::t('app', 'To confirm that this is a real person, and not a virtual account (fake or bot)') . '.',
            'relation' => Yii::t('app', 'To see social recommendations in the profiles of other users') . '.',
            'is_basic_income_candidate' => Yii::t('app', 'To confirm that this person meets the requirements to earn a weekly basic income') . '.',
        ];
    }

    public function beforeValidate()
    {
        if (!$this->userIdOrName) {
            $this->link_user_id = null;
        }

        return parent::beforeValidate();
    }

    /**
     * Validates the user exists.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validateUserExists($attribute)
    {
        $user = User::find()
            ->andWhere([
                'OR',
                ['id' => $this->userIdOrName],
                ['username' => $this->userIdOrName],
            ])
            ->one();

        if ($user) {
            $this->link_user_id = $user->id;
        } else {
            $this->addError('userIdOrName', Yii::t('app', 'User ID / Username doesn\'t exists.'));
        }
    }

    /**
     * Validates that user which will be linked and user-owner is not same.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validateLinkUserAndOwnerNotSame($attribute)
    {
        if ($this->link_user_id == $this->user_id) {
            $this->addError('userIdOrName', 'Contact owner and linked user cannot be same.');
        }
    }

    public function validateLinkUsedIdUnique($attribute)
    {
        $contactExists = Contact::find()
            ->andWhere([
                'link_user_id' => $this->link_user_id,
                'user_id' => Yii::$app->user->identity->id,
            ])
            ->andWhere([
                'not', ['id' => $this->id],
            ])
            ->exists();

        if ($contactExists) {
            $this->addError('userIdOrName', 'This User ID is already in use for another contact.');
        }
    }

    /**
     * @return DebtRedistributionQuery|ActiveQuery
     */
    public function getDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), [
            'user_id' => 'user_id',
            'link_user_id' => 'link_user_id',
        ]);
    }

    /**
     * Relation which require additional custom condition, to return exactly one row.
     *
     * @return DebtRedistributionQuery|ActiveQuery
     */
    public function getDebtRedistributionByDebtorCustom()
    {
        return $this->hasOne(DebtRedistribution::className(), [
            'user_id' => 'user_id',
            'link_user_id' => 'link_user_id',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery|ContactQuery
     */
    public function getChainMembers()
    {
        return $this->hasMany(self::className(), [
            'user_id' => 'link_user_id',
        ])->inverseOf('chainMemberParent');
    }

    /**
     * @return \yii\db\ActiveQuery|ContactQuery
     */
    public function getChainMemberParent()
    {
        /** @var [] $link empty array is not bug. {@see DebtBalance::$chainMemberParent} */
        $link = [];
        return $this->hasOne(self::className(), $link);
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

    public function isUser(): bool
    {
        return (bool)$this->link_user_id;
    }

    public function isNonUser(): bool
    {
        return !$this->isUser();
    }

    public function hasDebtTransferPriority(): bool
    {
        return (bool)$this->debt_redistribution_priority;
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

    //some magic here, moved out from beforeSave
    public function setUserIdOrName($idOrName)
    {
        $user = User::find()
            ->andWhere([
                'or',
                ['id' => $idOrName],
                ['username' => $this->idOrName]
            ])
            ->one();

        if ((!empty($user->contact)) && (((int) $user->contact->id !== (int) $this->id))) {
            $contact = $user->contact;
            $contact->link_user_id = null;
            $contact->save(false);
        }
    }

    /**
     * @param bool $insert
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
            $modelOld = clone $this;
            $modelOld->link_user_id = $oldId;

            $models = DebtRedistribution::find()
                ->usersByModelSource($modelOld)
                ->all();
            $this->deleteDebtRedistributions($models);
        }
    }

    /**
     * @param DebtRedistribution[] $models
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

    /**
     * @return \yii\db\ActiveQuery
     *
     * Get contact's groups
     */
    public function getGroups()
    {
        return $this->hasMany(ContactGroup::class, ['id' => 'contact_group_id'])
                    ->viaTable('contact_has_group', ['contact_id' => 'id'])
                    ->orderBy('name');
    }

    public function getGroupIds(): array
    {
        return ArrayHelper::getColumn($this->getGroups()->asArray()->all(), 'id');
    }

    public function getLinkUserId()
    {
        return $this->link_user_id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinkedUser()
    {
        if ($this->link_user_id) {
            return $this->hasOne(User::class, ['id' => 'link_user_id']);
        }

        return false;
    }

    public function getRelationBadge()
    {
        $classes = [
            0 => 'secondary',
            1 => 'success',
            2 => 'danger',
        ];

        return Html::badge($classes[(int)$this->relation], self::RELATION_LABELS[(int)$this->relation]);
    }

    public function getIsRealBadge()
    {
        $classes = [
            0 => 'secondary',
            1 => 'primary',
        ];

        return Html::badge($classes[(int)$this->is_real], self::IS_REAL_LABELS[(int)$this->is_real]);
    }

    public function getIsBasicincomeCandidateLabel()
    {
        return Yii::t('app', self::IS_BASIC_INCOME_CANDIDATE_LABELS[(int)$this->is_basic_income_candidate]);
    }
}
