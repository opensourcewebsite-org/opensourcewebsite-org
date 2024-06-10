<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_child_group".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $child_group_id
 * @property int $updated_by
 *
 * @property Chat $chat
 * @property Chat $childGroup
 * @property User $updatedBy
 *
 * @package app\modules\bot\models
 */
class ChatChildGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_child_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'child_group_id', 'updated_by'], 'required'],
            [['chat_id', 'child_group_id', 'updated_by'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['child_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['child_group_id' => 'id']],
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
            'chat_id' => 'Chat ID',
            'child_group_id' => 'Child Group ID',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[ChildGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChildGroup()
    {
        return $this->hasOne(Chat::class, ['id' => 'child_group_id']);
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
}
