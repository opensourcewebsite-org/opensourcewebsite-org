<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "group_gowords".
 *
 * @property int $_id
 * @property int $chat_id
 * @property string $text
 */
class GroupGoword extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_gowords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'chat_id' => 'ChatID',
            'text' => 'Text',
        ];
    }

    public function getId() {
        return $this->_id;
    }

    public function getChatId() {
    	return $this->chat_id;
    }

    public function setChatId($chat_id) {
        $this->chat_id = $chat_id;
    }

    public function getText() {
    	return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function accept($text) {
        return mb_stripos($text, $this->getText()) !== FALSE;
    }
}
