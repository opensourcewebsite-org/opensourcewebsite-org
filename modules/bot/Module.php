<?php

namespace app\modules\bot;

use Yii;
use app\modules\bot\components\CommandRouteResolver;
use app\modules\bot\components\api\BotApi;
use app\modules\bot\components\api\Types\Update;
use app\modules\bot\models\Bot;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\UserState;
use app\modules\bot\models\User as BotUser;
use yii\base\InvalidRouteException;
use app\models\User;
use app\models\Rating;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatSetting;

/**
 * OSW Bot module definition class
 * @link https://t.me/opensourcewebsite_bot
 * @property CommandRouteResolver $commandRouteResolver
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\bot\controllers';

    public $defaultControllerNamespace = null;

    public $defaultViewPath = null;

    /**
     * @var User
     */
    public $user;

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

                $botUser = BotUser::findOne([
                    'provider_user_id' => $this->getUpdate()->getFrom()->getId(),
                ]);

                if (!isset($botUser)) {
                    // Create bot user
                    $botUser = BotUser::createUser($this->getUpdate()->getFrom());

                    $isNewUser = true;
                }

                // Update telegram user information
                $botUser->updateInfo($this->getUpdate()->getFrom());

                // Set user language for bot answers
                Yii::$app->language = $botUser->language->code;

                if (!$botUser->save()) {
                    Yii::warning($botUser->getErrors());

                    return false;
                }
            }

            // create a bot user for new forward from
            if ($this->getUpdate()->getRequestMessage() && ($providerForwardFrom = $this->getUpdate()->getRequestMessage()->getForwardFrom())) {
                $forwardBotUser = BotUser::findOne([
                    'provider_user_id' => $providerForwardFrom->getId(),
                ]);

                if (!isset($forwardBotUser)) {
                    $forwardBotUser = BotUser::createUser($providerForwardFrom);
                }

                $forwardBotUser->updateInfo($providerForwardFrom);

                if (!$forwardBotUser->save()) {
                    Yii::warning($forwardBotUser->getErrors());

                    return false;
                }
            }

            $chat = Chat::findOne([
                'chat_id' => $this->getUpdate()->getChat()->getId(),
                'bot_id' => $this->getBot()->id,
            ]);

            $isNewChat = false;

            if (!isset($chat)) {
                $chat = new Chat();
                $chat->setAttributes([
                    'chat_id' => $this->getUpdate()->getChat()->getId(),
                    'bot_id' => $this->getBot()->id,
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
                return false;
            }

            $this->setChat($chat);

            $this->updateNamespaceByChat($this->getChat());

            // Save chat administrators for new group or channel
            if ($isNewChat && !$chat->isPrivate()) {
                $administrators = $this->getBotApi()->getChatAdministrators($chat->getChatId());

                foreach ($administrators as $administrator) {
                    $administratorBotUser = BotUser::findOne([
                        'provider_user_id' => $administrator->getUser()->getId(),
                    ]);

                    if (!isset($administratorBotUser)) {
                        $administratorUpdateUser = $administrator->getUser();

                        $administratorBotUser = BotUser::createUser($administratorUpdateUser);

                        // Update bot user information
                        $administratorBotUser->updateInfo($administratorUpdateUser);
                        $administratorBotUser->save();
                    }

                    $administratorBotUser->link('chats', $chat, [
                        'status' => $administrator->getStatus(),
                        'role' => $administrator->getStatus() == ChatMember::STATUS_CREATOR ? ChatMember::ROLE_ADMINISTRATOR : ChatMember::ROLE_MEMBER,
                    ]);
                }
            }

            if (isset($botUser)) {
                if (!$chatMember = $chat->getChatMemberByUser($botUser)) {
                    $telegramChatMember = $this->getBotApi()->getChatMember(
                        $chat->getChatId(),
                        $botUser->provider_user_id
                    );

                    if ($telegramChatMember) {
                        $chat->link('users', $botUser, [
                            'status' => $telegramChatMember->getStatus(),
                        ]);
                    }
                }

                if (!($user = $botUser->globalUser)) {
                    $user = User::createWithRandomPassword();
                    $user->name = $botUser->getFullName();

                    if ($isNewUser) {
                        if ($chat->isPrivate() && $this->getUpdate()->getRequestMessage()) {
                            $matches = [];

                            if (preg_match('/\/start (\d+)/', $this->getUpdate()->getRequestMessage()->getText(), $matches)) {
                                $user->referrer_id = $matches[1];
                            }
                        }
                    }

                    $user->save();

                    $botUser->user_id = $user->id;
                    $botUser->save();
                }

                $this->user = $user;
                $this->setBotUser($botUser);
                $this->setBotUserState(UserState::fromUser($botUser));

                if ($chat->isPrivate()) {
                    $this->user->updateLastActivity();
                    $this->getUpdate()->setPrivateMessageFromState($this->getBotUserState());
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
            $state = $this->getBotUserState()->getName();
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

        list($route, $params, $isStateRoute) = $this->commandRouteResolver->resolveRoute($this->getUpdate(), $state);

        if (!$isStateRoute && $this->getChat()->isPrivate()) {
            $this->getBotUserState()->setName($state);
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
                $this->getBotUserState()->setIntermediateField('private_message_ids', json_encode($privateMessageIds));
                $this->getBotUserState()->save($this->getBotUser());
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
            'bot_id' => $this->getBot()->id,
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
     * @return User|null
     */
    public function getBotUser()
    {
        if (Yii::$container->hasSingleton('botUser')) {
            return Yii::$container->get('botUser');
        }

        return null;
    }

    /**
     * @param BotUser $botUser
     *
     * @return BotUser
     */
    public function setBotUser(BotUser $botUser)
    {
        Yii::$container->setSingleton('botUser', $botUser);

        return $botUser;
    }

    /**
     * @return UserState|null
     */
    public function getBotUserState()
    {
        if (Yii::$container->hasSingleton('botUserState')) {
            return Yii::$container->get('botUserState');
        }

        return null;
    }

    /**
     * @param UserState $botUserState
     *
     * @return UserState
     */
    public function setBotUserState(UserState $botUserState)
    {
        Yii::$container->setSingleton('botUserState', $botUserState);

        return $botUserState;
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
