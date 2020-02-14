<?php

namespace app\models;

/**
 * This is the model class for table "bot_message_filter".
 *
 * @property int $id
 * @property int $provider_user_id
 * @property int $chat_id
 * @property string $filter_word
 */
class BotMessageFilter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_message_filter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provider_user_id', 'filter_word'], 'required'],
            [['provider_user_id', 'chat_id'], 'integer'],
            [['filter_word'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_user_id' => 'Provider User ID',
            'filter_word' => 'Filter Word',
        ];
    }

    /**
     * @return null|string
     */
    public static function getFilterPattern()
    {
        $data = self::findBySql('SELECT GROUP_CONCAT(DISTINCT(filter_word) SEPARATOR "|") as pattern FROM `bot_message_filter`')
            ->asArray()
            ->one();

        if (!empty($data['pattern'])) {
            return '(' . $data['pattern'] . ')';
        }

        return null;
    }

    /**
     * @param $message
     * @param $chatId
     * @param $userId
     *
     * @return bool
     */
    public static function addWord($message, $chatId, $userId)
    {
        $filterModel = new self();
        $filterModel->filter_word = $message;
        $filterModel->provider_user_id = $userId;
        $filterModel->chat_id = $chatId;

        return $filterModel->save();
    }

    /**
     * @param $message
     * @param $chatId
     * @param $userId
     *
     * @return bool
     */
    public static function removeWord($message, $chatId, $userId)
    {
        return self::deleteAll([
            'filter_word' => $message,
            'provider_user_id' => $userId,
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @param $chatId
     *
     * @return array
     */
    public static function getKeyBoardList($chatId)
    {
        $list = [];
        $words = BotMessageFilter::find()->where(['chat_id' => $chatId])->all();

        foreach ($words as $item) {
            $list[] = [
                '/filter remove ' . $item->filter_word,
            ];
        }

        return $list;
    }
}
