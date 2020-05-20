<?php

namespace app\modules\bot;

use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\request\CallbackQueryUpdateHandler;
use app\modules\bot\components\request\MessageUpdateHandler;
use Yii;
use app\modules\bot\components\api\BotApi;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\Chat;
use app\modules\bot\models\UserState;
use app\modules\bot\models\User as TelegramUser;
use yii\base\InvalidRouteException;
use app\models\User;
use app\models\Rating;
use app\modules\bot\components\Controller;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 * @property CommandRouteResolver $commandRouteResolver
 */
class Module extends \yii\base\Module
{
    /**
     * @var BotApi
     */
    private $botApi;

    /**
     * @var models\Bot
     */
    private $botInfo;

    /**
     * @var array
     */
    private $updateHandlers = [];

    /**
     * @var models\User
     */
    public $telegramUser;

    /**
     * @var models\Chat
     */
    public $telegramChat;

    /**
     * @var Update
     */
    public $update;

    /**
     * @var User
     */
    public $user;

    /**
     * @var models\UserState
     */
    public $userState;

    public function init()
    {
        parent::init();

        $this->updateHandlers = [
            new MessageUpdateHandler(),
            new CallbackQueryUpdateHandler(),
        ];
    }

    public function getBotApi()
    {
        return $this->botApi;
    }

    public function handleInput($input, $token)
    {
        $result = false;

        $updateArray = json_decode($input, true);
        $this->update = Update::fromResponse($updateArray);
        $this->botInfo = Bot::findOne(['token' => $token]);
        if ($this->botInfo) {
            $this->botApi = new BotApi($this->botInfo->token);

            if (isset(Yii::$app->params['telegramProxy'])) {
                $this->botApi->setProxy(Yii::$app->params['telegramProxy']);
            }

            if ($this->initialize($this->update, $this->botInfo->id)) {
                Yii::$app->language = $this->telegramUser->language->code;

                $result = $this->dispatchRoute($this->update);

                $this->save();
            }
        }
        return $result;
    }

    /**
     * @param $update
     * @param $botId
     * @return bool
     */
    private function initialize($update, $botId)
    {
        foreach ($this->updateHandlers as $updateHandler) {
            $updateUser = $updateHandler->getFrom($update);
            $updateChat = $updateHandler->getChat($update);
            if (isset($updateUser) && isset($updateChat)) {
                break;
            }
        }
        if (isset($updateUser) && isset($updateChat)) {
            $isNewUser = false;
            $telegramUser = TelegramUser::findOne(['provider_user_id' => $updateUser->getId()]);
            // Store telegram user if it doesn't exist yet
            if (!isset($telegramUser)) {
                $isNewUser = true;

                $telegramUser = TelegramUser::createUser($updateUser);
            }
            // Update telegram user information
            $telegramUser->updateInfo($updateUser);

            if (!$telegramUser->save()) {
                return false;
            }

            $telegramChat = Chat::findOne([
                'chat_id' => $updateChat->getId(),
                'bot_id' => $botId,
            ]);
            // Store telegram chat if it doesn't exist yet
            $newChat = false;
            if (!isset($telegramChat)) {
                $telegramChat = new Chat();
                $telegramChat->setAttributes([
                    'chat_id' => $updateChat->getId(),
                    'bot_id' => $botId,
                ]);

                $newChat = true;
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

            // Add chat administrators to db
            if ($newChat && $updateChat->getType() != Chat::TYPE_PRIVATE) {
                $administrators = $this->botApi->getChatAdministrators($updateChat->getId());

                foreach ($administrators as $administrator) {
                    $user = TelegramUser::findOne(['provider_user_id' => $administrator->getUser()->getId()]);

                    if (!isset($user)) {
                        $administratorUpdateUser = $administrator->getUser();

                        $user = TelegramUser::createUser($administratorUpdateUser);

                        // Update telegram user information
                        $user->updateInfo($administratorUpdateUser);
                    }

                    $user->link('chats', $telegramChat, ['status' => $administrator->getStatus()]);
                }
            }

            $type = $telegramChat->isPrivate() ? 'private' : 'public';
            Yii::configure($this, require __DIR__ . "/config/$type.php");

            // To separate commands for each type of chat
            $namespace = $telegramChat->isPrivate()
                ? Controller::TYPE_PRIVATE
                : Controller::TYPE_PUBLIC;
            $this->setupPaths($namespace);

            if (!$telegramChat->hasUser($telegramUser)) {
                $telegramChatMember = $this->botApi->getChatMember($telegramChat->chat_id, $telegramUser->provider_user_id);

                $telegramChat->link('users', $telegramUser, ['status' => $telegramChatMember->getStatus()]);
            }

            // $telegramChatMember = $this->botApi->getChatMember(
            //     $telegramChat->chat_id,
            //     $telegramUser->provider_user_id
            // );
            // $chatMember->setAttributes([
            //     'status' => $telegramChatMember->getStatus(),
            // ]);

            // $chatMember->save();

            if (!isset($telegramUser->user_id)) {
                $user = User::createWithRandomPassword();
                $user->name = $telegramUser->getFullName();

                if ($isNewUser) {
                    if ($message = $update->getMessage()) {
                        $matches = [];
                        if (preg_match('/\/start (\d+)/', $message->getText(), $matches)) {
                            $user->referrer_id = $matches[1];
                        }
                    }
                }

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

            $this->user = $user;
            $this->telegramUser = $telegramUser;
            $this->userState = UserState::fromUser($telegramUser);
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
     * @param Update $update
     * @return bool
     * @throws InvalidRouteException
     */
    public function dispatchRoute(Update $update)
    {
        $result = false;

        $state = $this->telegramChat->isPrivate()
            ? $this->userState->getName()
            : null;
        $defaultRoute = $this->telegramChat->isPrivate()
            ? 'default/command-not-found'
            : 'message/index';
        list($route, $params, $isStateRoute) = $this->commandRouteResolver->resolveRoute($update, $state, $defaultRoute);
        if (array_key_exists('botname', $params) && !empty($params['botname']) && $params['botname'] !== $this->botInfo->name) {
            return $result;
        }
        if (!$isStateRoute) {
            $this->userState->setName(null);
        }
        /* Temporary solution for filter in groups */
        if (!isset($route) && !$this->telegramChat->isPrivate()) {
            $route = $defaultRoute;
        }
        if ($route) {
            try {
                $commands = $this->runAction($route, $params);
            } catch (InvalidRouteException $e) {
                $commands = $this->runAction($defaultRoute);
            }

            if (isset($commands) && is_array($commands)) {
                foreach ($commands as $command) {
                    try {
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

    public function getBotName()
    {
        return $this->botInfo->name;
    }
}
