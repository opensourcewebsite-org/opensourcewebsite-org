<?php

namespace app\models;

use TelegramBot\Api\BotApi;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class BotHandler
 *
 * @package app\models
 *
 * @property int $chat_id
 * @property int $bot_id
 * @property int $user_id
 * @property int $user_name
 * @property int $support_group_id
 * @property string $token
 * @property string $language
 * @property string $command
 * @property string $_language_code
 * @property bool is_bot
 */
class BotHandler extends BotApi
{

    public $token;
    public $chat_id;
    public $language;
    public $is_bot;
    public $command;
    public $support_group_id;
    public $bot_id;
    public $user_id;
    public $user_name;
    protected $_language_code;

    /**
     * @param string $language
     *
     * @return void
     */
    protected function setLanguageCode($language)
    {
        #default language
        $this->_language_code = 'en';

        if ($baseLanguage = Language::findOne(['code' => $language])) {
            $this->_language_code = $baseLanguage->code;
        }

        $userLanguage = SupportGroupBotClient::find()
            ->where(['provider_bot_user_id' => $this->user_id])
            ->with('supportGroupClient')
            ->one();

        # if user used command /lang we override _language_code
        if ($userLanguage) {
            $this->_language_code = $userLanguage->supportGroupClient->language_code;
        }
    }

    /**
     * @param \app\models\SupportGroupCommandText[] $commands
     *
     * @return bool
     */
    protected function generateResponse($commands)
    {

        if (!$commands) {
            return false;
        }

        $this->setLanguageCode($this->language);

        $getLanguage = ArrayHelper::map($commands, 'language_code', 'text');

        if (ArrayHelper::keyExists($this->_language_code, $getLanguage)) {
            $output = $getLanguage[$this->_language_code];

            $this->sendMessage($this->chat_id, $output);

            return true;
        }

        # get first command from array;
        $output = $commands[0];

        $this->sendMessage($this->chat_id, $output);

        return true;
    }

    /**
     * @return bool
     */
    protected function generateDefaultResponse()
    {
        $default = SupportGroupCommand::find()
            ->where(['token' => $this->token])
            ->andWhere(['is_default' => 1])
            ->joinWith([
                'supportGroupBot',
                'supportGroupCommandTexts',
            ])
            ->one();

        # there is no default commands, nothing is returned
        if (!$default) {
            return false;
        }

        return $this->generateResponse($default->supportGroupCommandTexts);
    }

    /**
     * @return bool
     */
    public function executeLangCommand()
    {
        $availableLanguages = SupportGroupLanguage::find()
            ->select('language_code')
            ->where(['support_group_id' => $this->support_group_id])
            ->column();

        $lang = substr($this->command, 1, mb_strlen($this->command));

        if ($this->command == '/lang') {
            $output = "Choose your language.\n";

            $output .= '/' . implode("\n/", $availableLanguages);

            $this->sendMessage($this->chat_id, $output);

            return true;
        } elseif (in_array($lang, $availableLanguages)) {
            $userLanguage = SupportGroupBotClient::find()
                ->where(['provider_bot_user_id' => $this->user_id])
                ->with('supportGroupClient')
                ->one();

            $supportGroup = $userLanguage->supportGroupClient;
            $supportGroup->language_code = $lang;
            $supportGroup->save();

            return $this->generateDefaultResponse();
        } elseif (Language::findOne(['code' => $lang])) {
            # If not existed language. Nothing happen and no code run
            exit;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function executeCommand()
    {
        $commands = SupportGroupCommand::find()
            ->where(['token' => $this->token])
            ->andWhere(['command' => $this->command])
            ->joinWith([
                'supportGroupBot',
                'supportGroupCommandTexts',
            ])
            ->one();

        if (!$commands) {
            return $this->generateDefaultResponse();
        }

        return $this->generateResponse($commands->supportGroupCommandTexts);
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveClientInfo()
    {
        $this->setLanguageCode($this->language);

        if (SupportGroupBotClient::findOne(['provider_bot_user_id' => $this->user_id])) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction('SERIALIZABLE');

        $client = new SupportGroupClient();
        $client->setAttributes([
            'support_group_id' => $this->support_group_id,
            'language_code'    => $this->_language_code,
        ]);

        if ($client->save()) {
            $botClient = new SupportGroupBotClient();
            $botClient->setAttributes([
                'support_group_bot_id'      => $this->bot_id,
                'support_group_client_id'   => $client->id,
                'provider_bot_user_id'      => $this->user_id,
                'provider_bot_user_name'    => $this->user_name,
                'provider_bot_user_blocked' => 0,
            ]);

            if ($botClient->save()) {
                return $transaction->commit();
            }
        }

        return $transaction->rollBack();
    }
}
