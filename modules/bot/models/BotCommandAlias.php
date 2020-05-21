<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_command_alias".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $command
 * @property string $text
 */
class BotCommandAlias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_command_alias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'command', 'text'], 'required'],
            [['chat_id'], 'integer'],
            [['command', 'text'], 'string', 'max' => 255],
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
            'command' => 'Command',
            'text' => 'Text',
        ];
    }
}
