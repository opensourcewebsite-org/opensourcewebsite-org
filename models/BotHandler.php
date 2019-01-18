<?php

namespace app\models;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class BotHandler
 *
 * @package app\models
 *
 * @property array $_request
 * @property int $support_group_id
 * @property int $bot_id
 * @property int $_longitude
 * @property int $_latitude
 * @property int $_location_at
 * @property string $_language_code
 */
class BotHandler extends BotApi
{
    /**
     * Telegram request
     */
    protected $_request;

    /**
     * Inside support group ID
     */
    public $support_group_id;

    /**
     * Inside support bot ID
     */
    public $bot_id;

    /**
     * Logic param for language detection
     */
    protected $_language_code;

    /**
     * users geo data Longitude
     */
    protected $_longitude = null;

    /**
     * users geo data Latitude
     */
    protected $_latitude = null;

    /**
     * Time when geo location set
     */
    protected $_location_at = null;


    /**
     * Constructor
     *
     * @param string $token Telegram Bot API token
     * @param string|null $trackerToken Yandex AppMetrica application api_key
     * @param array $request
     */
    public function __construct($token, $request, $trackerToken = null)
    {
        parent::__construct($token, $trackerToken = null);

        $this->_request = $request;
    }

    /**
     * @return \TelegramBot\Api\Types\Message
     */
    public function getMessage()
    {
        return Message::fromResponse($this->_request['message']);
    }

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
            ->where(['provider_bot_user_id' => $this->getMessage()->getFrom()->getId()])
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
     * @return void
     */
    protected function setGeoData()
    {
        if ($location = $this->getMessage()->getLocation()) {
            $this->_longitude = $location->getLongitude();
            $this->_latitude = $location->getLatitude();
            $this->_location_at = time();
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

        $this->setLanguageCode($this->getMessage()->getFrom()->getLanguageCode());

        $getLanguage = ArrayHelper::map($commands, 'language_code', 'text');

        if (ArrayHelper::keyExists($this->_language_code, $getLanguage)) {
            $output = $getLanguage[$this->_language_code];

            $this->sendMessage($this->getMessage()->getChat()->getId(), $output);

            return true;
        }

        # get first command from array;
        $output = $commands[0];

        $this->sendMessage($this->getMessage()->getChat()->getId(), $output->text);

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

        $lang = substr($this->getMessage()->getText(), 1, mb_strlen($this->getMessage()->getText()));

        # first we check if user tried to set up a language
        if (in_array($lang, $availableLanguages)) {
            $userLanguage = SupportGroupBotClient::find()
                ->where(['provider_bot_user_id' => $this->getMessage()->getFrom()->getId()])
                ->with('supportGroupClient')
                ->one();

            $supportGroup = $userLanguage->supportGroupClient;
            $supportGroup->language_code = $lang;
            $supportGroup->save();

            return $this->generateDefaultResponse();
        } elseif ($this->getMessage()->getText() == '/lang' || $this->_language_code == null) {
            $output = "Choose your language.\n";

            $availableLanguagesName = SupportGroupLanguage::find()
                ->where(['support_group_id' => $this->support_group_id])
                ->with('languageCode')
                ->all();

            $availableLanguagesName = ArrayHelper::map(
                $availableLanguagesName,
                'language_code',
                'languageCode.name'
            );

            foreach ($availableLanguages as $languageShow) {
                $output .= '/' . $languageShow . ' ' . $availableLanguagesName[$languageShow] . "\n";
            }

            $this->sendMessage($this->getMessage()->getChat()->getId(), $output);

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
            ->andWhere(['command' => $this->getMessage()->getText()])
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

        $this->setGeoData();
        $this->setLanguageCode($this->getMessage()->getFrom()->getLanguageCode());

        if ($existedClient = SupportGroupBotClient::find()
            ->where(['provider_bot_user_id' => $this->getMessage()->getFrom()->getId()])
            ->with('supportGroupClient')
            ->one()
        ) {
            $transaction = Yii::$app->db->beginTransaction('SERIALIZABLE');
            # owner/member disabled his language
            if ($this->_language_code == null) {
                $existedClientLanguage = $existedClient->supportGroupClient;
                $existedClientLanguage->language_code = $this->_language_code;
                if (!$existedClientLanguage->save()) {
                    $transaction->rollBack();
                }
            }

            # update geo position
            if (!is_null($this->_longitude) && !is_null($this->_latitude) &&
                ($existedClient->location_lon != $this->_longitude ||
                    $existedClient->location_lat != $this->_latitude)
            ) {
                if (!$existedClient->save()) {
                    $transaction->rollBack();
                }
            }

            $transaction->commit();

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
                'provider_bot_user_id'      => $this->getMessage()->getFrom()->getId(),
                'provider_bot_user_name'    => $this->getMessage()->getFrom()->getUsername(),
                'location_lon'              => $this->_longitude,
                'location_lat'              => $this->_latitude,
                'location_at'               => $this->_location_at,
                'provider_bot_user_blocked' => 0,
            ]);

            if ($botClient->save()) {
                return $transaction->commit();
            }
        }

        return $transaction->rollBack();
    }
}
