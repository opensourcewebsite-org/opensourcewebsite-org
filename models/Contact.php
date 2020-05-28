<?php

namespace app\models;

use voskobovich\linker\LinkerBehavior;
use voskobovich\linker\updaters\ManyToManyUpdater;
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

    public const DEBT_REDISTRIBUTION_PRIORITY_NO = 0;
    public const DEBT_REDISTRIBUTION_PRIORITY_MAX = 255;

    const VIEW_USER = 1;
    const VIEW_VIRTUALS = 2;
    const RELATIONS = [
        0 => 'Neutral',
        1 => 'Friend',
        2 => 'Enemy',
    ];

    public $userIdOrName;

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                /*
                 * This behavior makes it easy to maintain many-to-many and one-to-many relations
                 */
                'class' => LinkerBehavior::class,
                'relations' => [
                    'contact_group_ids' => [
                        'contactGroups',
                        'updater' => [
                            'class' => ManyToManyUpdater::class,
                        ],
                    ],
                ],
            ],
            ]
        );
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        /*
         * Prepare for LinkerBehavior
         */
        if (is_array($this->contact_group_ids)) {
            $contactGroupIds = [];
            foreach ($this->contact_group_ids as $contact_group_id) {
                if (!is_numeric($contact_group_id)) {
                    $this->validateHasEmptyGroup();
                    $contactGroup = new ContactGroup(['name' => $contact_group_id, 'user_id' => Yii::$app->user->identity->id]);
                    if ($contactGroup->save()) {
                        $contactGroupIds[] = $contactGroup->id;
                    }
                } else {
                    $contactGroupIds[] = $contact_group_id;
                }
            }
            $this->contact_group_ids = $contactGroupIds;
        }

        return true;
    }

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
            [['contact_group_ids'], 'each', 'rule' => ['integer']],
            [['user_id', 'link_user_id', 'is_real', 'relation'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['name', 'required',
                'when' => static function (self $model) {
                    return empty($model->userIdOrName);
                },
                'whenClient' => "function (attribute, value) {
                    return $('#contact-useridorname').val() == '';
                }",
            ],
            ['debt_redistribution_priority', 'integer', 'min' => 0, 'max' => self::DEBT_REDISTRIBUTION_PRIORITY_MAX],
            ['debt_redistribution_priority', 'filter', 'filter' => static function ($v) { return ((int)$v) ?: 0; }],
            ['vote_delegation_priority', 'integer', 'min' => 0, 'max' => 255],
            ['vote_delegation_priority', 'filter', 'filter' => static function ($v) { return ((int)$v) ?: null; }],
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
            'userIdOrName' => Yii::t('user', 'User ID') . ' / ' . Yii::t('user', 'Username'),
            'is_real' => Yii::t('app', 'Is Real'),
            'relation' => Yii::t('app', 'Relation'),
            'vote_delegation_priority' => Yii::t('app', 'Vote Delegation Priority'),
            'debt_redistribution_priority' => Yii::t('app', 'Debt Redistribution Priority'),
        ];
    }

    public function attributeHints()
    {
        return [
            'debt_redistribution_priority' => Html::ul([
                '"1" - the highest.',
                "Note: it has no affect, if field \"{$this->getAttributeLabel('userIdOrName')}\" is empty",
            ]),
            'vote_delegation_priority' => Html::ul([
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

    /**
     * @return DebtRedistributionQuery|ActiveQuery
     */
    public function getDebtRedistributions()
    {
        return $this->hasMany(DebtRedistribution::className(), [
            'user_id' => DebtRedistribution::getOwnerAttribute(),
            'link_user_id' => DebtRedistribution::getLinkedAttribute(),
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
            'user_id' => DebtRedistribution::getDebtorAttribute(),
            'link_user_id' => DebtRedistribution::getDebtReceiverAttribute(),
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

    /**
     * @return \yii\db\ActiveQuery
     *
     * Get contact's groups
     */
    public function getContactGroups()
    {
        return $this->hasMany(ContactGroup::class, ['id' => 'contact_group_id'])
                    ->viaTable('contact_has_group', ['contact_id' => 'id']);

    }

/*
 * validate count empty groups
 */
    public function validateHasEmptyGroup()
    {
        if(Yii::$app->user->identity->hasEmptyContactGroup) {
            $this->addError('contact_group_ids', 'You already have an empty group!');
            return;
        }
    }
}
