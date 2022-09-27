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
    public const NAMESPACE_PRIVATE = 'privates';
    public const NAMESPACE_GROUP = 'groups';
    public const NAMESPACE_CHANNEL = 'channels';

    public $namespace = null;

    public $defaultControllerNamespace = null;

    public $defaultViewPath = null;

    public function init()
    {
        parent::init();

        $this->defaultControllerNamespace = $this->controllerNamespace;
        $this->defaultViewPath = $this->getViewPath();
        $this->setBot(new Bot());
    }

    /**
     * @param string $input
     * @param string $token Bot token
     * @return bool
     */
    public function handleInput($input, $token = null)
    {
        $updateArray = json_decode($input, true);

        if (empty($updateArray) || !$token) {
            return false;
        }

        $this->setUpdate(Update::fromResponse($updateArray));

        if ($this->getBot()->token == $token) {
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

                    if (!$globalForwardUser->validate('name')) {
                        $globalForwardUser->name = null;
                    }

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
            ]);

            $isNewChat = false;

            if (!isset($chat)) {
                $chat = new Chat();

                $chat->setAttributes([
                    'chat_id' => $this->getUpdate()->getChat()->getId(),
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

                    if (!$globalUser->validate('name')) {
                        $globalUser->name = null;
                    }

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
        $state = null;

        if ($this->getChat()->isPrivate()) {
            $state = $this->getUserState()->getName();
            // Delete the user message in private chat
            if ($this->getUpdate()->getMessage()) {
                $this->getBotApi()->deleteMessage(
                    $this->getChat()->getChatId(),
                    $this->getUpdate()->getMessage()->getMessageId()
                );
            // Ignore editing the user message in private chat
            } elseif ($this->getUpdate()->getEditedMessage()) {
                return true;
            }
        } elseif ($this->getChat()->isGroup()) {
            // Ignore service user id, that also acts as sender of channel posts forwarded to discussion groups
            if ($this->getUpdate()->getFrom()->getId() == User::ANONYMOUS_LINKED_CHANNEL_PROVIDER_USER_ID) {
                return true;
            }
        }

        list($route, $params, $isStateRoute) = $this->commandRouteResolver->resolveRoute($this->getUpdate(), $state);

        if ($this->getChat()->isPrivate()) {
            if (!$isStateRoute) {
                $this->getUserState()->setName($state);
            }
        // Ignore other botname if present
        } elseif ($this->getChat()->isGroup() || $this->getChat()->isChannel()) {
            if (isset($params['botname']) && $params['botname'] && ($params['botname'] != $this->getBot()->getUsername())) {
                return true;
            }
        }

        try {
            $response = $this->runAction($route, $params);
        } catch (InvalidRouteException $e) {
            $response = $this->runAction($this->commandRouteResolver->defaultRoute);
        }

        return true;
    }

    /**
     * @param int $chatId
     * @return Chat|bool
     */
    public function setChatByChatId($chatId)
    {
        $chat = Chat::findOne([
            'chat_id' => $chatId,
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
     * @return Chat
     */
    public function setChat(Chat $chat)
    {
        Yii::$container->setSingleton('chat', $chat);

        $this->updateNamespaceByChat($chat);

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
     * @return Bot
     */
    public function setBot(Bot $bot)
    {
        Yii::$container->setSingleton('bot', $bot);

        return $bot;
    }

    /**
     * @return BotApi|null
     */
    public function getBotApi()
    {
        if ($bot = $this->getBot()) {
            return $bot->getBotApi();
        }

        return null;
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
     * @return Update
     */
    public function setUpdate(Update $update)
    {
        Yii::$container->setSingleton('update', $update);

        return $update;
    }

    /**
     * @param Chat $chat
     * @return boolean
     */
    public function updateNamespaceByChat(Chat $chat)
    {
        if ($chat) {
            // Choose namespace
            switch (true) {
                case $chat->isPrivate():
                    $namespace = self::NAMESPACE_PRIVATE;

                    break;
                case $chat->isGroup():
                    $namespace = self::NAMESPACE_GROUP;

                    break;
                case $chat->isChannel():
                    $namespace = self::NAMESPACE_CHANNEL;

                    break;
                default:
                    $namespace = null;

                    break;
            }
            // Set namespace
            if ($namespace && ($this->namespace != $namespace)) {
                $this->namespace = $namespace;

                Yii::configure($this, require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $this->namespace . '.php');
                $this->controllerNamespace = $this->defaultControllerNamespace . '\\' . $this->namespace;
                $this->setViewPath($this->defaultViewPath . DIRECTORY_SEPARATOR . $this->namespace);
            }

            return true;
        } else {
            return false;
        }
    }
}
