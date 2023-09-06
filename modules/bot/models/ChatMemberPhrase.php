<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_chat_member_phrase".
 *
 * @property int $id
 * @property int $member_id
 * @property int $phrase_id
 *
 * @property ChatMember $chatMember
 * @property ChatPhrase $chatPhrase
 */
class ChatMemberPhrase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_member_phrase}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'phrase_id'], 'required'],
            [['member_id', 'phrase_id'], 'integer'],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatMember::class, 'targetAttribute' => ['member_id' => 'id']],
            [['phrase_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatPhrase::class, 'targetAttribute' => ['phrase_id' => 'id']],
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
            'phrase_id' => 'Phrase ID',
        ];
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
     * Gets query for [[ChatPhrase]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChatPhrase()
    {
        return $this->hasOne(ChatPhrase::class, ['id' => 'phrase_id']);
    }
}
