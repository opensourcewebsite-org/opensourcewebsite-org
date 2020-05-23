<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_route_alias".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $route
 * @property string $text
 */
class BotRouteAlias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_route_alias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'route', 'text'], 'required'],
            [['chat_id'], 'integer'],
            [['route', 'text'], 'string', 'max' => 255],
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
            'route' => 'Route',
            'text' => 'Text',
        ];
    }
}
