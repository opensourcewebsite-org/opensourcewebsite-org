<?php

namespace app\modules\bot;

use app\models\Rating;
use app\models\User as GlobalUser;
use app\modules\bot\components\api\BotApi;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\Bot;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\UserState;
use Yii;
use yii\base\InvalidRouteException;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\bot\controllers';

    public $defaultControllerNamespace = null;

    public $defaultViewPath = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->defaultControllerNamespace = $this->controllerNamespace;
        $this->defaultViewPath = $this->getViewPath();
    }

    /**
     * @param string $input
     * @param string $token Bot token
     *
     * @return bool
     */
    public function handleInput($input, $token)
    {
        $updateArray = json_decode($input, true);

        if (empty($updateArray)) {
            return false;
        }

        $this->setUpdate(Update::fromResponse($updateArray));
        // TODO refactoring
        $this->getUpdate()->__construct();
        $bot = Bot::findOne([
            'token' => $token,
        ]);

        if ($bot) {
            $this->setBot($bot);

            if ($this->initFromUpdate()) {
                $this->dispatchRoute();
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function initFromUpdate()
    {
        if ($this->getUpdate()->getChat()) {
            if ($this->getUpdate()->getFrom()) {
                $isNewUser = false;

                $user = User::findOne([
                    'provider_user_id' => $this->getUpdate()->getFrom()->getId(),
                ]);

                if (!isset($user)) {
                    // Create bot user
                    $user = User::createUser($this->getUpdate()->getFrom());

                    $isNewUser = true;
                }
                // Update telegram user information
                $user->updateInfo($this->getUpdate()->getFrom());
                // Set user language for bot answers
                Yii::$app->language = $user->language->code;

                if (!$user->save()) {
                    Yii::warning($user->getErrors());

                    return false;
                }
            }

            // create a user for new forward from
            if ($this->getUpdate()->getRequestMessage() && ($providerForwardFrom = $this->getUpdate()->getRequestMessage()->getForwardFrom())) {
                $forwardUser = User::findOne([
                    'provider_user_id' => $providerForwardFrom->getId(),
                ]);

                if (!isset($forwardUser)) {
                    $forwardUser = User::createUser($providerForwardFrom);
                }

                $forwardUser->updateInfo($providerForwardFrom);

                if (!$forwardUser->save()) {
                    Yii::warning($forwardUser->getErrors());

                    return false;
                }

                if (!$globalForwardUser = $forwardUser->globalUser) {
                    $globalForwardUser = GlobalUser::createWithRandomPassword();
                    $globalForwardUser->name = $forwardUser->getFullName();

                    if (!$globalForwardUser->save()) {
                        Yii::warning($globalForwardUser->getErrors());

                        return false;
                    }

                    $forwardUser->user_id = $globalForwardUser->id;
                    $forwardUser->save();
                }
            }

            $chat = Chat::findOne([
                'chat_id' => $this->getUpdate()->getChat()->getId(),
                'bot_id' => $this->getBot()->getId(),
            ]);

            $isNewChat = false;

            if (!isset($chat)) {
                $chat = new Chat();
                $chat->setAttributes([
                    'chat_id' => $this->getUpdate()->getChat()->getId(),
                    'bot_id' => $this->getBot()->getId(),
                ]);

                $isNewChat = true;
            }
            // Update chat information
            $chat->setAttributes([
                'type' => $this->getUpdate()->getChat()->getType(),
                'title' => $this->getUpdate()->getChat()->getTitle(),
                'username' => $this->getUpdate()->getChat()->getUsername(),
                'first_name' => $this->getUpdate()->getChat()->getFirstName(),
                'last_name' => $this->getUpdate()->getChat()->getLastName(),
            ]);

            if (!$chat->save()) {
                Yii::warning($chat->getErrors());

                return false;
            }

            $this->setChat($chat);

            $this->updateNamespaceByChat($this->getChat());

            // Save chat administrators for new group or channel
            if ($isNewChat && !$chat->isPrivate()) {
                $botApiAdministrators = $this->getBotApi()->getChatAdministrators($chat->getChatId());

                foreach ($botApiAdministrators as $botApiAdministrator) {
                    $administrator = User::findOne([
                        'provider_user_id' => $botApiAdministrator->getUser()->getId(),
                    ]);

                    if (!isset($administrator)) {
                        $botApiUser = $botApiAdministrator->getUser();

                        $administrator = User::createUser($botApiUser);

                        // Update user information
                        $administrator->updateInfo($botApiUser);
                        $administrator->save();
                    }

                    $administrator->link('chats', $chat, [
                        'status' => $botApiAdministrator->getStatus(),
                        'role' => $botApiAdministrator->getStatus() == ChatMember::STATUS_CREATOR ? ChatMember::ROLE_ADMINISTRATOR : ChatMember::ROLE_MEMBER,
                    ]);
                }
            }

            if (isset($user)) {
                if (!$chatMember = $chat->getChatMemberByUser($user)) {
                    $telegramChatMember = $this->getBotApi()->getChatMember(
                        $chat->getChatId(),
                        $user->provider_user_id
                    );

                    if ($telegramChatMember) {
                        $chat->link('users', $user, [
                            'status' => $telegramChatMember->getStatus(),
                        ]);
                    }
                }

                if (!$globalUser = $user->globalUser) {
                    $globalUser = GlobalUser::createWithRandomPassword();
                    $globalUser->name = $user->getFullName();

                    if ($isNewUser) {
                        if ($chat->isPrivate() && $this->getUpdate()->getRequestMessage()) {
                            $matches = [];

                            if (preg_match('/\/start (\d+)/', $this->getUpdate()->getRequestMessage()->getText(), $matches)) {
                                $globalUser->referrer_id = $matches[1];
                            }
                        }
                    }

                    if (!$globalUser->save()) {
                        Yii::warning($globalUser->getErrors());

                        return false;
                    }

                    $user->user_id = $globalUser->id;
                    $user->save();
                }

                Yii::$app->user->setIdentity($globalUser);

                $this->setGlobalUser($globalUser);
                $this->setUser($user);
                $this->setUserState(UserState::fromUser($user));

                if ($chat->isPrivate()) {
                    $globalUser->updateLastActivity();
                    $this->getUpdate()->setPrivateMessageFromState($this->getUserState());
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws InvalidRouteException
     */
    private function dispatchRoute()
    {
        if ($this->getChat()->isPrivate()) {
            $state = $this->getUserState()->getName();
            // Delete all user messages in private chat
            if ($this->getUpdate()->getMessage()) {
                $this->getBotApi()->deleteMessage(
                    $this->getChat()->getChatId(),
                    $this->getUpdate()->getMessage()->getMessageId()
                );
            }
        } else {
            $state = null;
        }

        if ($this->getChat()->isGroup()) {
            // Telegram service user id, that also acts as sender of channel posts forwarded to discussion groups
            if ($this->getUpdate()->getFrom()->getId() == 777000) {
                return true;
            }
        }

        list($route, $params, $isStateRoute) = $this->commandRouteResolver->resolveRoute($this->getUpdate(), $state);

        if (!$isStateRoute && $this->getChat()->isPrivate()) {
            $this->getUserState()->setName($state);
        }

        try {
            $commands = $this->runAction($route, $params);
        } catch (InvalidRouteException $e) {
            $commands = $this->runAction($this->commandRouteResolver->defaultRoute);
        }

        if (isset($commands) && is_array($commands)) {
            $privateMessageIds = [];
            foreach ($commands as $command) {
                try {
                    $command->send($this->getBotApi());
                    // Remember ids of all bot messages in private chat to delete them later
                    if ($this->getChat()->isPrivate()) {
                        if ($messageId = $command->getMessageId()) {
                            $privateMessageIds []= $messageId;
                        }
                    }
                } catch (\Exception $e) {
                    Yii::error("[$route] [" . get_class($command) . '] ' . $e->getCode() . ' ' . $e->getMessage(), 'bot');
                }
            }

            if ($this->getChat()->isPrivate()) {
                $this->getUserState()->setIntermediateField('private_message_ids', json_encode($privateMessageIds));
                $this->getUserState()->save($this->getUser());
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function initFromConsole()
    {
        return $this->updateNamespaceByChat($this->getChat());
    }

    /**
     * @param int $chatId
     *
     * @return Chat|null
     */
    public function setChatByChatId($chatId)
    {
        $chat = Chat::findOne([
            'chat_id' => $chatId,
            'bot_id' => $this->getBot()->getId(),
        ]);

        if ($chat) {
            return $this->setChat($chat);
        }

        return false;
    }

    /**
     * @return Chat|null
     */
    public function getChat()
    {
        if (Yii::$container->hasSingleton('chat')) {
            return Yii::$container->get('chat');
        }

        return null;
    }

    /**
     * @param Chat $chat
     *
     * @return Chat
     */
    public function setChat(Chat $chat)
    {
        Yii::$container->setSingleton('chat', $chat);

        return $chat;
    }

    /**
     * @return Bot|null
     */
    public function getBot()
    {
        if (Yii::$container->hasSingleton('bot')) {
            return Yii::$container->get('bot');
        }

        return null;
    }

    /**
     * @param Bot $bot
     *
     * @return Bot
     */
    public function setBot(Bot $bot)
    {
        Yii::$container->setSingleton('bot', $bot);

        return $bot;
    }

    /**
     * @return BotApi
     */
    public function getBotApi()
    {
        if (Yii::$container->hasSingleton('botApi')) {
            return Yii::$container->get('botApi');
        } elseif ($this->getBot()) {
            $botApi = new BotApi($this->getBot()->token);

            if ($botApi) {
                if (isset(Yii::$app->params['telegramProxy'])) {
                    $botApi->setProxy(Yii::$app->params['telegramProxy']);
                }

                return $this->setBotApi($botApi);
            }
        }

        return null;
    }

    /**
     * @param BotApi $botApi
     *
     * @return BotApi
     */
    public function setBotApi(BotApi $botApi)
    {
        Yii::$container->setSingleton('botApi', $botApi);

        return $botApi;
    }

    /**
     * @return GlobalUser|null
     */
    public function getGlobalUser()
    {
        if (Yii::$container->hasSingleton('globalUser')) {
            return Yii::$container->get('globalUser');
        }

        return null;
    }

    /**
     * @param GlobalUser $globalUser
     *
     * @return GlobalUser
     */
    public function setGlobalUser(GlobalUser $globalUser)
    {
        Yii::$container->setSingleton('globalUser', $globalUser);

        return $globalUser;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (Yii::$container->hasSingleton('user')) {
            return Yii::$container->get('user');
        }

        return null;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function setUser(User $user)
    {
        Yii::$container->setSingleton('user', $user);

        return $user;
    }

    /**
     * @return UserState|null
     */
    public function getUserState()
    {
        if (Yii::$container->hasSingleton('userState')) {
            return Yii::$container->get('userState');
        }

        return null;
    }

    /**
     * @param UserState $userState
     *
     * @return UserState
     */
    public function setUserState(UserState $userState)
    {
        Yii::$container->setSingleton('userState', $userState);

        return $userState;
    }

    /**
     * @return Update|null
     */
    public function getUpdate()
    {
        if (Yii::$container->hasSingleton('update')) {
            return Yii::$container->get('update');
        }

        return null;
    }

    /**
     * @param Update $update
     *
     * @return Update
     */
    public function setUpdate(Update $update)
    {
        Yii::$container->setSingleton('update', $update);

        return $update;
    }

    /**
     * @param Chat $chat
     *
     * @return boolean
     */
    public function updateNamespaceByChat(Chat $chat)
    {
        if ($chat) {
            // Choose namespace
            if ($chat->isPrivate()) {
                $namespace = 'privates';
            } elseif ($chat->isGroup()) {
                $namespace = 'groups';
            } elseif ($chat->isChannel()) {
                $namespace = 'channels';
            }
            // Set namespace
            Yii::configure($this, require __DIR__ . "/config/$namespace.php");
            $this->controllerNamespace = $this->defaultControllerNamespace . '\\' . $namespace;
            $this->setViewPath($this->defaultViewPath . '/' . $namespace);

            return true;
        } else {
            return false;
        }
    }
}
