<?php

namespace app\modules\bot\controllers\privates;

use app\models\StellarServer;
use app\models\UserStellar;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use Yii;

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
        if (!($userStellar = $this->globalUser->userStellar)) {
            return $this->actionSetPublicKey();
        }

        if ($userStellar->isExpired()) {
            $userStellar->delete();
            unset($this->globalUser->userStellar);

            return $this->actionSetPublicKey();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'stellar' => $userStellar,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm'),
                            'text' => Yii::t('bot', 'CONFIRM'),
                            'visible' => !$userStellar->isConfirmed(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('basic-income'),
                            'text' => Yii::t('bot', 'Basic Income'),
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
                            'visible' => $userStellar->isConfirmed(),
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

        $userStellar = $this->globalUser->userStellar ?: $this->globalUser->newUserStellar;

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if ($userStellar->isNewRecord || ($userStellar->public_key != $text)) {
                    $userStellar->public_key = $text;

                    if ($userStellar->save()) {
                        unset($this->globalUser->userStellar);

                        return $this->actionIndex();
                    }
                }
            }
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set-public-key'),
                [
                    [
                        [
                            'callback_data' => (!$userStellar->isNewRecord ? self::createRoute() : MyAccountController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionDelete(): array
    {
        if ($userStellar = $this->globalUser->userStellar) {
            $userStellar->delete();
            unset($this->globalUser->userStellar);
        }

        return $this->actionIndex();
    }

    public function actionConfirm(): array
    {
        if (!($userStellar = $this->globalUser->userStellar) || $userStellar->isConfirmed()) {
            return $this->actionIndex();
        }

        if ($stellarServer = new StellarServer()) {
            if (!$stellarServer->accountExists($userStellar->getPublicKey())) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-account-not-found'),
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

            $userStellar->confirm();
            unset($this->globalUser->userStellar);
        }

        return $this->actionIndex();
    }

    public function actionGroups(): array
    {
        if (!($userStellar = $this->globalUser->userStellar) || !$userStellar->isConfirmed()) {
            return $this->actionIndex();
        }

        if ($stellarServer = new StellarServer()) {
            if (!$account = $stellarServer->getAccount($userStellar->getPublicKey())) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-account-not-found'),
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

    public function actionBasicIncome(): array
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('basic-income', [
                    'user' => $this->globalUser,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-basic-income-status'),
                            'text' => $this->globalUser->isBasicIncomeOn() ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/commands/StellarGiverController.php',
                            'text' => Yii::t('bot', 'Source Code'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyStellarController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSetBasicIncomeStatus()
    {
        if ($this->globalUser->basic_income_on) {
            $this->globalUser->basic_income_on = 0;
        } else {
            $this->globalUser->basic_income_on = 1;
        }

        $this->globalUser->save();

        return $this->actionBasicIncome();
    }

    public function actionDepositIncome(): array
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('deposit-income'),
                [
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/commands/StellarOperatorController.php',
                            'text' => Yii::t('bot', 'Source Code'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyStellarController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
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
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/commands/StellarCroupierController.php',
                            'text' => Yii::t('bot', 'Source Code'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyStellarController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
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
