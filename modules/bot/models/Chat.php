<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Chat extends ActiveRecord
{
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public static function tableName()
    {
        return 'bot_chat';
    }

    public function rules()
    {
        return [
            [['type', 'bot_id', 'chat_id'], 'required'],
            [['id', 'chat_id', 'bot_id'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function isPrivate()
    {
        return $this->type == self::TYPE_PRIVATE;
    }

    public function getPhrases()
    {
        return $this->hasMany(Phrase::className(), ['chat_id' => 'id']);
    }

    public function getBlacklistPhrases()
    {
        return $this->getPhrases()->where(['type' => self::FILTER_MODE_BLACKLIST])->all();
    }

    public function getWhitelistPhrases()
    {
        return $this->getPhrases()->where(['type' => self::FILTER_MODE_WHITELIST])->all();
    }

    public function getSettings()
    {
        return $this->hasMany(ChatSetting::className(), ['chat_id' => 'id']);
    }

    public function getSetting($setting)
    {
        return $this->getSettings()->where(['setting' => $setting])->one();
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id']);
    }

    public function getAdminUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id'], function($query) {
            $query->andWhere(['or', ['status' => ChatMember::STATUS_CREATOR], ['status' => ChatMember::STATUS_ADMINISTRATOR]]);
        });
    }
}
