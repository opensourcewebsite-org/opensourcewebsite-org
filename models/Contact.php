<?php

namespace app\models;

use app\components\helpers\Html;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\interfaces\UserRelation\ByOwnerTrait;
use app\models\queries\ContactQuery;
use app\models\queries\DebtRedistributionQuery;
use app\models\traits\SelectForUpdateTrait;
use app\modules\bot\models\User as BotUser;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
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

    public $userIdOrName;

    public $groupIds = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%contact}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'link_user_id', 'relation'], 'integer'],
            [['relation'], 'integer', 'min' => 0, 'max' => 2],
            ['userIdOrName', 'string', 'on' => 'form'],
            ['userIdOrName', 'trim', 'on' => 'form'],
            ['userIdOrName', 'required',
                'when' => static function (self $model) {
                    return empty($model->name);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-name').val() == '';
                }",
                'message' => $this->getAttributeLabel('userIdOrName') . ' cannot be blank if ' . $this->getAttributeLabel('name') . ' is empty.',
                'on' => 'form',
            ],
            ['userIdOrName', 'validateUserExists', 'on' => 'form'],
            ['link_user_id', 'validateLinkUserAndOwnerNotSame'],
            ['link_user_id', 'validateLinkUserIdUnique'],
            ['name', 'required',
                'when' => static function (self $model) {
                    return empty($model->userIdOrName);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-useridorname').val() == '';
                }",
                'message' => $this->getAttributeLabel('name') . ' cannot be blank if ' . $this->getAttributeLabel('userIdOrName') . ' is empty.',
                'on' => 'form',
            ],
            ['name', 'string', 'max' => 255],
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
                'filter' => static function ($value) {
                    return ((int)$value) ?: 0;
                },
            ],
            ['is_real', 'boolean'],
            [['is_real', 'relation'], 'default', 'value' => 0],
            [
                'groupIds', 'filter', 'filter' => function ($value) {
                    if ($value === '') {
                        return [];
                    }

                    return $value;
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
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'link_user_id' => 'Link User ID',
            'name' => Yii::t('user', 'Name'),
            'userIdOrName' => 'User ID / Username',
            'is_real' => Yii::t('app', 'Personal identification'),
            'relation' => Yii::t('app', 'Personal relation'),
            'vote_delegation_priority' => Yii::t('app', 'Vote delegation priority'),
            'debt_redistribution_priority' => Yii::t('app', 'Debt transfer priority'),
            'groupIds' => Yii::t('app', 'Groups'),
        ];
    }

    public function attributeHints(): array
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
        ];
    }

    public function afterFind()
    {
        $this->userIdOrName = $this->link_user_id;

        parent::afterFind();
    }

    public function beforeValidate()
    {
        if ($this->getScenario() == 'form') {
            if (!$this->userIdOrName) {
                $this->link_user_id = null;
            }
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
            return false;
        }
        return true;
    }

    public function validateLinkUserIdUnique($attribute)
    {
        $contactExists = Contact::find()
            ->andWhere([
                'link_user_id' => $this->link_user_id,
                'user_id' => $this->user_id,
            ])
            ->andWhere([
                'not', ['id' => $this->id],
            ])
            ->exists();

        if ($contactExists) {
            $this->addError('userIdOrName', 'This User ID is already in use for another contact.');
            return false;
        }
        return true;
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

    public function getChainMembers(): ActiveQuery
    {
        return $this->hasMany(self::className(), [
            'user_id' => 'link_user_id',
        ])
        ->inverseOf('chainMemberParent');
    }

    public function getChainMemberParent(): ActiveQuery
    {
        /** @var [] $link empty array is not bug. {@see DebtBalance::$chainMemberParent} */
        $link = [];

        return $this->hasOne(self::className(), $link);
    }

    public function getDisplayName()
    {
        if ($this->link_user_id) {
            if ($this->linkedUser->username) {
                $name = '@' . $this->linkedUser->username;
            } else {
                $name = '#' . $this->link_user_id;
            }

            $name .= ($this->name ? ' - ' . $this->name : '');
        } else {
            $name = $this->name ?: $this->id;
        }

        return $name;
    }

    public function getTelegramDisplayName()
    {
        if ($this->link_user_id) {
            if ($this->counterBotUser) {
                if ($this->counterBotUser->provider_user_name) {
                    $name = '@' . $this->counterBotUser->provider_user_name;
                } else {
                    $name = '#' . $this->link_user_id;
                }
            } else {
                if ($this->linkedUser->username) {
                    $name = '@' . $this->linkedUser->username;
                } else {
                    $name = '#' . $this->link_user_id;
                }
            }

            $name .= ($this->name ? ' - ' . $this->name : '');
        } else {
            $name = $this->name ?: $this->id;
        }

        return $name;
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
        return [
            self::SCENARIO_DEFAULT => self::OP_DELETE | self::OP_UPDATE,
            'form' => self::OP_DELETE | self::OP_UPDATE,
        ];
    }

    public function getGroups(): ActiveQuery
    {
        return $this->hasMany(ContactGroup::class, ['id' => 'contact_group_id'])
                    ->viaTable('contact_has_group', ['contact_id' => 'id'])
                    ->orderBy('name');
    }

    public function getGroupIds(): array
    {
        return ArrayHelper::getColumn($this->getGroups()->asArray()->all(), 'id');
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId(int $userId)
    {
        $this->user_id = $userId;
    }

    public function getLinkUserId()
    {
        return $this->link_user_id;
    }

    public function setLinkUserId(int $userId)
    {
        $this->link_user_id = $userId;
    }

    public function getLinkedUser(): ?ActiveQuery
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

        return Html::badge($classes[(int)$this->relation], $this->getRelationLabel());
    }

    public function getRelationLabel(): string
    {
        return static::getRelationLabels()[(int)$this->relation];
    }

    public static function getRelationLabels(): array
    {
        return [
            0 => Yii::t('app', 'Neutral'),
            1 => Yii::t('app', 'Positive'),
            2 => Yii::t('app', 'Negative'),
        ];
    }

    public function getIsRealBadge()
    {
        $classes = [
            0 => 'secondary',
            1 => 'primary',
        ];

        return Html::badge($classes[(int)$this->is_real], $this->getIsRealLabel());
    }

    public function getIsRealLabel(): string
    {
        return static::getIsRealLabels()[(int)$this->is_real];
    }

    public static function getIsRealLabels(): array
    {
        return [
            0 => Yii::t('app', 'Virtual'),
            1 => Yii::t('app', 'Real'),
        ];
    }

    public function getCounterBotUser(): ActiveQuery
    {
        return $this->hasOne(BotUser::className(), ['user_id' => 'link_user_id']);
    }
}
