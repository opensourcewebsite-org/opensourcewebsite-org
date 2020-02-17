<?php

namespace app\modules\bot;

use Yii;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\Chat;
use app\modules\bot\models\UserState;
use app\modules\bot\models\User as TelegramUser;
use yii\base\InvalidRouteException;
use app\models\User;
use app\models\Language;
use app\models\Rating;
use app\modules\bot\components\ReplyKeyboardManager;
use app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use app\modules\bot\components\Controller;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 */
class Module extends \yii\base\Module
{
    /**
     * @var \TelegramBot\Api\BotApi
     */
    private $botApi;

    /**
     * @var models\User
     */
    public $telegramUser;

    /**
     * @var models\chat
     */
    public $telegramChat;

    /**
     * @var \TelegramBot\Api\Types\Update
     */
    public $update;

    /**
     * @var \app\models\User
     */
    public $user;

    /**
     * @var models\UserState
     */
    public $userState;

    public function init()
    {
        parent::init();

        Yii::configure($this, require __DIR__ . '/config.php');
    }

    public function handleInput($input, $token)
    {
        $result = false;

        $updateArray = json_decode($input, true);
        $this->update = Update::fromResponse($updateArray);
        $botInfo = Bot::findOne(['token' => $token]);
        if ($botInfo) {
            $this->botApi = new BotApi($botInfo->token);

            if (isset(Yii::$app->params['telegramProxy'])) {
                $this->botApi->setProxy(Yii::$app->params['telegramProxy']);
            }

            if ($this->initialize($this->update, $botInfo->id)) {
                Yii::$app->language = $this->telegramUser->language_code;

                $result = $this->dispatchRoute($this->update);

                $this->save();
            }
        }
        return $result;
    }

    /**
     * @param $update \TelegramBot\Api\Types\Update
     *
     * @return bool
     */
    private function initialize($update, $botId)
    {
        foreach ($this->commandRouteResolver->requestHandlers as $requestHandler) {
            $updateUser = $requestHandler->getFrom($update);
            $updateChat = $requestHandler->getChat($update);
            if (isset($updateUser) && isset($updateChat)) {
                break;
            }
        }

        if (isset($updateUser) && isset($updateChat)) {
            $telegramUser = TelegramUser::findOne(['provider_user_id' => $updateUser->getId()]);
            // Store telegram user if it doesn't exist yet
            if (!isset($telegramUser)) {
                $language = Language::findOne([
                    'code' => $updateUser->getLanguageCode(),
                ]);
                $languageCode = isset($language) ? $language->code : 'en';

                $telegramUser = new TelegramUser();
                $telegramUser->setAttributes([
                    'provider_user_id' => $updateUser->getId(),
                    'language_code' => $languageCode,
                ]);
            }
            // Update telegram user information
            $telegramUser->setAttributes([
                'provider_user_name' => $updateUser->getUsername(),
                'provider_user_first_name' => $updateUser->getFirstName(),
                'provider_user_last_name' => $updateUser->getLastName(),
                'provider_bot_user_blocked' => 0,
                'last_message_at' => time(),
            ]);
            if (!$telegramUser->save()) {
                return false;
            }

            $telegramChat = Chat::findOne([
                'chat_id' => $updateChat->getId(),
                'bot_id' => $botId,
            ]);
            // Store telegram chat if it doesn't exist yet
            if (!isset($telegramChat)) {
                $isNewChat = true;
                $telegramChat = new Chat();
                $telegramChat->setAttributes([
                    'chat_id' => $updateChat->getId(),
                    'bot_id' => $botId,
                ]);
            }
            // Update telegram chat information
            $telegramChat->setAttributes([
                'type' => $updateChat->getType(),
                'title' => $updateChat->getTitle(),
                'username' => $updateChat->getUsername(),
                'first_name' => $updateChat->getFirstName(),
                'last_name' => $updateChat->getLastName(),
            ]);
            if (!$telegramChat->save()) {
                return false;
            }

            // To separate commands for each type of chat
            $namespace = $telegramChat->isPrivate()
                ? Controller::TYPE_PRIVATE
                : Controller::TYPE_PUBLIC;
            $this->setupPaths($namespace);

            if (isset($isNewChat) && $isNewChat) {
                $telegramChat->link('users', $telegramUser);
            }

            if (!isset($telegramUser->user_id)) {
                $user = User::createWithRandomPassword();
                $user->name = $telegramUser->getFullName();

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->save();
                    $telegramUser->user_id = $user->id;
                    $telegramUser->save();
                    $user->addRating(Rating::USE_TELEGRAM_BOT, 1, false);

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return false;
                }
            } else {
                $user = User::findOne($telegramUser->user_id);
            }

            $userState = UserState::fromUser($telegramUser);

            $keyboardButtons = $userState->getKeyboardButtons();
            ReplyKeyboardManager::init($keyboardButtons);
            ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
                'text' => '⚙️',
                ReplyKeyboardManager::REPLYKEYBOARDBUTTON_IS_CONSTANT => true,
            ]);

            $this->user = $user;
            $this->telegramUser = $telegramUser;
            $this->userState = $userState;
            $this->telegramChat = $telegramChat;

            return true;
        }

        return false;
    }

    private function save()
    {
        $this->user->save();
        $this->telegramChat->save();
        $this->userState->save($this->telegramUser);
    }

    private function setupPaths($namespace)
    {
        $this->controllerNamespace .= '\\' . $namespace;
        $this->setViewPath($this->getViewPath() . '/' . $namespace);
    }

    /**
     * @param $update \TelegramBot\Api\Types\Update
     *
     * @return bool
     */
    private function dispatchRoute($update)
    {
        $result = false;

        $state = $this->telegramChat->isPrivate()
            ? $this->userState->getName()
            : null;
        list($route, $params, $isStateRoute) = $this->commandRouteResolver->resolveRoute($update, $state);
        if (!$isStateRoute) {
            $this->userState->setName(null);
        }
        if ($route) {
            try {
                $commands = $this->runAction($route, $params);
            } catch (InvalidRouteException $e) {
                if ($this->telegramChat->isPrivate()) {
                    $commands = $this->runAction('default/command-not-found');
                }
            }

            if (isset($commands) && is_array($commands)) {
                foreach ($commands as $command) {
                    try {
                        $replyMarkup = $command->replyMarkup;
                        if (ReplyKeyboardManager::getInstance()->isChanged()
                            && $command instanceof SendMessageCommand
                            && !isset($replyMarkup)) {
                            $this->setReplyKeyboard($command);
                        }
                        $command->send($this->botApi);
                    } catch (\Exception $ex) {
                        Yii::error("[$route] [" . get_class($command) . '] ' . $ex->getCode() . ' ' . $ex->getMessage(), 'bot');
                    }
                }

                $result = true;
            }
        }

        return $result;
    }

    private function setReplyKeyboard(&$command)
    {
        $keyboardButtons = ReplyKeyboardManager::getInstance()->getKeyboardButtons();
        $command->replyMarkup = (!empty($keyboardButtons))
            ? new ReplyKeyboardMarkup($keyboardButtons, false, true)
            : new ReplyKeyboardRemove();

        $this->userState->setKeyboardButtons($keyboardButtons);
    }
}
