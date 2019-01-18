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
    /**
     * Token bot telegram
     */
    public $token;

    /**
     * Chat room ID
     */
    public $chat_id;

    /**
     * Language from telegram chat
     */
    public $language;

    /**
     * Is this user bot?
     */
    public $is_bot;

    /**
     * Passed command
     */
    public $command;

    /**
     * Inside support group ID
     */
    public $support_group_id;

    /**
     * Inside support bot ID
     */
    public $bot_id;

    /**
     * Telegram user ID
     */
    public $user_id;

    /**
     * Telegram user name
     */
    public $user_name;

    /**
     * Logic param for language detection
     */
    protected $_language_code;


    /**
     * @param string $language
     *
     * @return void
     */
    protected function setLanguageCode($language)
    {
        $this->_language_code = null;

        $baseLanguage = SupportGroupLanguage::findOne([
            'language_code'    => $language,
            'support_group_id' => $this->support_group_id,
        ]);

        # case: when group has only 1 language
        $all_languages = SupportGroupLanguage::findAll(['support_group_id' => $this->support_group_id]);
        if (count($all_languages) == 1) {
            $this->_language_code = $all_languages[0]->language_code;
        }

        #default language
        if ($baseLanguage) {
            $this->_language_code = $baseLanguage->language_code;
        }

        $userLanguage = SupportGroupBotClient::find()
            ->where(['provider_bot_user_id' => $this->user_id])
            ->with('supportGroupClient')
            ->one();

        # if user used command /lang used before, we override _language_code
        if ($userLanguage) {
            $this->_language_code = $userLanguage->supportGroupClient->language_code;
        }

        # if owner/member deleted user's language
        if ($userLanguage && !is_null($userLanguage->supportGroupClient->language_code)) {
            $is_disabled = SupportGroupLanguage::findOne([
                'language_code'    => $userLanguage->supportGroupClient->language_code,
                'support_group_id' => $this->support_group_id,
            ]);

            if (!$is_disabled) {
                $this->_language_code = null;
            }
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

        $this->sendMessage($this->chat_id, $output->text);

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

        # first we check if user tried to set up a language
        if (in_array($lang, $availableLanguages)) {
            $userLanguage = SupportGroupBotClient::find()
                ->where(['provider_bot_user_id' => $this->user_id])
                ->with('supportGroupClient')
                ->one();

            $supportGroup = $userLanguage->supportGroupClient;
            $supportGroup->language_code = $lang;
            $supportGroup->save();

            return $this->generateDefaultResponse();
        } elseif ($this->command == '/lang' || $this->_language_code == null) {
            $output = "Choose your language.\n";

            $output .= '/' . implode("\n/", $availableLanguages);

            $this->sendMessage($this->chat_id, $output);

            return true;
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

        if ($existedClient = SupportGroupBotClient::find()
            ->where(['provider_bot_user_id' => $this->user_id])
            ->with('supportGroupClient')
            ->one()
        ) {
            # owner/member disabled his language
            if ($this->_language_code == null) {
                $existedClientLanguage = $existedClient->supportGroupClient;
                $existedClientLanguage->language_code = $this->_language_code;
                $existedClientLanguage->save();
            }

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
