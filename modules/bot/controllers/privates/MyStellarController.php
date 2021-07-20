<?php

namespace app\modules\bot\controllers\privates;

use app\models\StellarServer;
use app\models\UserStellar;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use Yii;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\Chat;

/**
 * Class MyStellarController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyStellarController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex(): array
    {
        $this->getState()->setName(null);
        $user = $this->getUser();

        if (!isset($user->stellar)) {
            return $this->actionSetPublicKey();
        }

        if ($user->stellar->isExpired()) {
            $user->stellar->delete();
            unset($user->stellar);

            return $this->actionSetPublicKey();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'stellar' => $user->stellar,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm'),
                            'text' => Yii::t('bot', 'CONFIRM'),
                            'visible' => !$user->stellar->isConfirmed(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('deposit-income'),
                            'text' => Yii::t('bot', 'Deposit Income'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('fortune-game'),
                            'text' => Yii::t('bot', 'Fortune Game'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('groups'),
                            'text' => Yii::t('bot', 'Telegram groups'),
                            'visible' => $user->stellar->isConfirmed(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'callback_data' => self::createRoute('set-public-key'),
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => self::createRoute('delete'),
                            'text' => Emoji::DELETE,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSetPublicKey(): array
    {
        $this->getState()->setName(self::createRoute('set-public-key'));
        $user = $this->getUser();

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if (isset($user->stellar)) {
                    if ($user->stellar->public_key != $text) {
                        $user->stellar->public_key = $text;
                        $user->stellar->created_at = time();
                        $user->stellar->confirmed_at = null;
                    }

                    $userStellar = $user->stellar;
                } else {
                    $userStellar = new UserStellar();

                    $userStellar->user_id = $user->id;
                    $userStellar->public_key = $text;
                }

                if ($userStellar->save()) {
                    unset($user->stellar);

                    return $this->actionIndex();
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-public-key'),
                [
                    [
                        [
                            'callback_data' => ($user->stellar ? self::createRoute() : MyAccountController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete(): array
    {
        $user = $this->getUser();

        if (isset($user->stellar)) {
            $user->stellar->delete();
            unset($user->stellar);
        }

        return $this->actionIndex();
    }

    public function actionConfirm(): array
    {
        $user = $this->getUser();

        if (!isset($user->stellar) || $user->stellar->isConfirmed()) {
            return $this->actionIndex();
        }

        $userStellar = $user->stellar;

        if ($stellarServer = new StellarServer()) {
            if (!$stellarServer->accountExists($userStellar->getPublicKey())) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-account-doesnt-exist'),
                        true
                    )
                    ->build();
            }

            $userSentTransaction = $stellarServer->operationExists(
                $userStellar->getPublicKey(),
                StellarServer::getDistributorPublicKey(),
                $userStellar->created_at,
                $userStellar->created_at + UserStellar::CONFIRM_REQUEST_LIFETIME
            );

            if (!$userSentTransaction) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-transaction-not-found'),
                        true
                    )
                    ->build();
            }

            $userStellar->confirmed_at = time();
            $userStellar->save();
            unset($user->stellar);
        }

        return $this->actionIndex();
    }

    public function actionGroups(): array
    {
        $user = $this->getUser();

        if (!isset($user->stellar) || !$user->stellar->isConfirmed()) {
            return $this->actionIndex();
        }

        $userStellar = $user->stellar;

        if ($stellarServer = new StellarServer()) {
            if (!$account = $stellarServer->getAccount($userStellar->getPublicKey())) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-account-doesnt-exist'),
                        true
                    )
                    ->build();
            }

            $buttons = [];

            // check telegram groups for holders
            foreach ($account->getBalances() as $asset) {
                if (($asset->getBalance() > 0) && (!$asset->isNativeAsset())) {
                    $chatSettings = ChatSetting::find()
                            ->where([
                                'setting' => 'stellar_asset',
                                'value' => $asset->getAssetCode(),
                            ])
                            ->all();

                    foreach ($chatSettings as $chatSetting) {
                        if ($chatSetting && ($chat = Chat::findOne($chatSetting->getChatId()))) {
                            if (($chat->stellar_status == ChatSetting::STATUS_ON)
                                    && ($chat->stellar_mode == ChatSetting::STELLAR_MODE_HOLDERS)
                                    && ($chat->stellar_issuer == $asset->getAssetIssuerAccountId())
                                    && ($chat->stellar_threshold <= $asset->getBalance())
                                    && $chat->stellar_invite_link) {
                                $buttons[][] = [
                                        'url' => $chat->stellar_invite_link,
                                        'text' => $chat->title,
                                    ];
                            }
                        }
                    }
                }
            }

            // check telegram groups for signers
            if ($signedAccounts = $stellarServer->getAccountsForSigner($userStellar->getPublicKey())) {
                foreach ($signedAccounts as $signedAccount) {
                    $chatSettings = ChatSetting::find()
                            ->where([
                                'setting' => 'stellar_issuer',
                                'value' => $signedAccount->getAccountId(),
                            ])
                            ->all();

                    foreach ($chatSettings as $chatSetting) {
                        if ($chatSetting && ($chat = Chat::findOne($chatSetting->getChatId()))) {
                            if (($chat->stellar_status == ChatSetting::STATUS_ON)
                                    && ($chat->stellar_mode == ChatSetting::STELLAR_MODE_SIGNERS)
                                    && $chat->stellar_invite_link) {
                                $buttons[][] = [
                                        'url' => $chat->stellar_invite_link,
                                        'text' => $chat->title,
                                    ];
                            }
                        }
                    }
                }
            }

            if (!$buttons) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-groups-not-found'),
                        true
                    )
                    ->build();
            }

            $buttons[] = [
                    [
                        'callback_data' => MyStellarController::createRoute(),
                        'text' => Emoji::BACK,
                    ],
                    [
                        'callback_data' => MenuController::createRoute(),
                        'text' => Emoji::MENU,
                    ],
                ];

            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('groups'),
                    $buttons
                )
                ->build();
        }

        return $this->actionIndex();
    }

    public function actionDepositIncome(): array
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('deposit-income'),
                [
                    [
                        [
                            'callback_data' => MyStellarController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionFortuneGame(): array
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('fortune-game'),
                [
                    [
                        [
                            'callback_data' => MyStellarController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
