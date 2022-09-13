<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_marketplace_link".
 *
 * @property int $id
 * @property int $member_id
 * @property string|null $title
 * @property string|null $url
 * @property int $updated_by
 *
 * @property ChatMember $chatMember
 * @property User $updatedBy
 */
class ChatMarketplaceLink extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_marketplace_link}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'updated_by'], 'required'],
            [['member_id', 'updated_by'], 'integer'],
            [['title', 'url'], 'string', 'max' => 255],
            ['url', 'url'],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatMember::class, 'targetAttribute' => ['member_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'title' => 'Title',
            'url' => 'Url',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // TODO refactoring
        $this->updated_by = Yii::$app->getModule('bot')->getUser()->getId();

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // TODO refactoring
        if (empty($this->updated_by)) {
            $this->updated_by = Yii::$app->getModule('bot')->getUser()->getId();
        }

        return parent::beforeValidate();
    }

    /**
     * Gets query for [[ChatMember]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChatMember()
    {
        return $this->hasOne(ChatMember::class, ['id' => 'member_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id'])
            ->viaTable(ChatMember::tableName(), ['id' => 'member_id']);
    }
}
