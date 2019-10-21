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
 * @property int $bot_client_id
 * @property int $type
 * @property float $_longitude
 * @property float $_latitude
 * @property int $_location_at
 * @property string $_language_code
 */
class SupportGroupBotHandler extends BotApi
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
     * Inside support client ID
     */
    public $bot_client_id;

    /**
     * Message type
     *
     * available types:
     *  - 1 : Ordinary text
     *  - 2 : Command
     */
    public $type;

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
        parent::__construct($token, $trackerToken);

        $this->_request = $request;
    }

    /**
     * @return \TelegramBot\Api\Types\Message
     */
    public function getMessage()
    {
        if (isset($this->_request['message'])) {
            $request = $this->_request['message'];
        } else {
            $request = $this->_request['edited_message'];
        }

        return Message::fromResponse($request);
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
            'language_code' => $language,
            'support_group_id' => $this->support_group_id,
        ]);

        # case: when group has only 1 language
        $languages = $this->getLanguagesByGroup();
        if (count($languages) == 1) {
            $this->_language_code = $languages[0]->language_code;
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
        if ($userLanguage &&
            !is_null($userLanguage->supportGroupClient->language_code)
        ) {
            $this->_language_code = $userLanguage->supportGroupClient->language_code;
        }

        # if owner/member deleted user's language
        if ($userLanguage && !is_null($userLanguage->supportGroupClient->language_code)) {
            $is_disabled = SupportGroupLanguage::findOne([
                'language_code' => $userLanguage->supportGroupClient->language_code,
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
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
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
     * @param bool $default_response
     *
     * @return bool|string
     */
    public function executeLangCommand($default_response = true)
    {
        $availableLanguages = SupportGroupLanguage::find()
            ->select('language_code')
            ->where(['support_group_id' => $this->support_group_id])
            ->column();

        $lang = substr(
            trim($this->getMessage()->getText()),
            1,
            mb_strlen(trim($this->getMessage()->getText()))
        );

        # first we check if user tried to set up a language
        if (in_array($lang, $availableLanguages)) {
            $userLanguage = SupportGroupBotClient::find()
                ->where(['provider_bot_user_id' => $this->getMessage()->getFrom()->getId()])
                ->with('supportGroupClient')
                ->one();

            $supportGroup = $userLanguage->supportGroupClient;
            $supportGroup->language_code = $lang;
            $supportGroup->save();

            return $default_response ? $this->generateDefaultResponse() : true;
        } elseif (trim($this->getMessage()->getText()) == '/lang' || $this->_language_code == null) {
            # when group has only 1 language
            $languages = $this->getLanguagesByGroup();
            if (count($languages) == 1)  {
                #if command /land setting  send our response
                $commands = $this->checkCommandLangByGroup();
                if ($commands) {
                   return $this->generateResponse($commands->supportGroupCommandTexts);
                }
                #if command /land not setting  send defult response
                return $this->generateDefaultResponse();
            }

            $output = '';

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
        $commands = $this->getCommandsByGroup();

        if (!$commands) {
            return $this->generateDefaultResponse();
        }

        return $this->generateResponse($commands->supportGroupCommandTexts);
    }

    /**
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function saveClientInfo()
    {

        $update = [];

        $this->setGeoData();
        $this->setLanguageCode($this->getMessage()->getFrom()->getLanguageCode());

        if ($existedClient = SupportGroupBotClient::find()
            ->where(['provider_bot_user_id' => $this->getMessage()->getFrom()->getId()])
            ->andWhere(['support_group_bot_id' => $this->bot_id])
            ->with('supportGroupClient')
            ->one()
        ) {
            $transaction = Yii::$app->db->beginTransaction('SERIALIZABLE');

            if (substr(trim($this->getMessage()->getText()), 0, 1) != '/' &&
                !$this->getMessage()->getLocation()) {
                $update = [
                    'last_message_at' => time()
                ];
            }

            $existedClient->setAttributes(ArrayHelper::merge([
                'provider_bot_user_blocked' => 0,
                'provider_bot_user_name' => $this->getMessage()->getFrom()->getUsername(),
                'provider_bot_user_first_name' => $this->getMessage()->getFrom()->getFirstName(),
                'provider_bot_user_last_name' => $this->getMessage()->getFrom()->getLastName()
            ], $update));

            if (!$existedClient->save()) {
                $transaction->rollBack();

                return false;
            }

            # owner/member disabled his language
            $existedClientLanguage = $existedClient->supportGroupClient;
            $existedClientLanguage->language_code = $this->_language_code;
            if (!$existedClientLanguage->save()) {
                $transaction->rollBack();

                return false;
            }

            # update geo position (Live location)
            if ($this->_longitude && $this->_latitude) {
                $existedClient->location_lon = $this->_longitude;
                $existedClient->location_lat = $this->_latitude;
                $existedClient->location_at = $this->_location_at;

                $existedClient->validate();

                if (!$existedClient->save()) {
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();

            return $existedClient->id;
        }

        $transaction = Yii::$app->db->beginTransaction('SERIALIZABLE');

        $client = new SupportGroupClient();
        $client->setAttributes([
            'support_group_id' => $this->support_group_id,
            'language_code' => $this->_language_code,
        ]);

        if ($client->save()) {
            $botClient = new SupportGroupBotClient();
            $botClient->setAttributes([
                'support_group_bot_id' => $this->bot_id,
                'support_group_client_id' => $client->id,
                'provider_bot_user_id' => $this->getMessage()->getFrom()->getId(),
                'provider_bot_user_name' => $this->getMessage()->getFrom()->getUsername(),
                'location_lon' => $this->_longitude,
                'location_lat' => $this->_latitude,
                'provider_bot_user_first_name' => $this->getMessage()->getFrom()->getFirstName(),
                'provider_bot_user_last_name' => $this->getMessage()->getFrom()->getLastName(),
                'location_at' => $this->_location_at,
                'provider_bot_user_blocked' => 0,
            ]);

            if ($botClient->save()) {
                $transaction->commit();

                return $botClient->id;
            }
        }

        $transaction->rollBack();

        return false;
    }

    /**
     * @return bool
     */
    public function saveOutsideMessage()
    {

        $text = $this->cleanEmoji(trim($this->getMessage()->getText()));

        if (mb_strlen($text) == 0) {
            return false;
        }

        $model = new SupportGroupOutsideMessage();
        $model->setAttributes([
            'support_group_bot_id' => $this->bot_id,
            'support_group_bot_client_id' => $this->bot_client_id,
            'type' => $this->type,
            'provider_message_id' => $this->getMessage()->getMessageId(),
            'message' => $text,
        ]);

        return $model->save();
    }

    /**
     * @param string $text
     * @return string
     */
    protected function cleanEmoji($text)
    {
        return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F415}](?:\x{200D}\x{1F9BA})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BD})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9AF})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FA}\x{1F7E0}-\x{1F7EB}\x{1F90D}-\x{1F93A}\x{1F93C}-\x{1F945}\x{1F947}-\x{1F971}\x{1F973}-\x{1F976}\x{1F97A}-\x{1F9A2}\x{1F9A5}-\x{1F9AA}\x{1F9AE}-\x{1F9CA}\x{1F9CD}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}]/u', '', $text);
    }

    /**
     * @return array
     */
    private function getLanguagesByGroup()
    {
        return SupportGroupLanguage::findAll(['support_group_id' => $this->support_group_id]);
    }

    /**
     * @return mixed
     */
    private function getCommandsByGroup()
    {
        $commands = SupportGroupCommand::find()
        ->where(['token' => $this->token])
        ->andWhere(['command' => trim($this->getMessage()->getText())])
        ->joinWith([
            'supportGroupBot',
            'supportGroupCommandTexts',
        ])
        ->one();

        return $commands;
    }

    /**
     * @return mixed
     */
    private function checkCommandLangByGroup()
    {
        $commands = $this->getCommandsByGroup();

        if ($commands['command'] === '/lang') {
            return $commands;
        }

    }

    /**
     * @return bool
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function executeExchangeRateCommand()
    {
        preg_match('/^(?<type>[a-zA-Z]+)\s(?<amount>\d+)\s(?<code>[a-zA-Z]+)$/', $this->getMessage()->getText(), $matches);
        if (isset($matches[0])) {
            $exchangeRate = SupportGroupExchangeRate::find()
                ->where(['token' => $this->token])
                ->andWhere(['is_default' => 1])
                ->joinWith([
                    'supportGroupBot',
                ])
                ->one();
            $rate = $exchangeRate->selling_rate;
            if (!strcasecmp($matches['type'], 'buy')) {
                $rate = $exchangeRate->buying_rate;
            }
            $message = $matches['amount'] . ' ' . $matches['code'] . ' = ' . $rate . ' ' . $exchangeRate->code;
            $this->sendMessage($this->getMessage()->getChat()->getId(), $message);
        }

        return true;
    }
}
