<?php

namespace app\modules\bot\controllers\privates;

use app\models\StellarServer;
use app\models\UserStellar;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageText;
use DateTime;
use Yii;
use ZuluCrypto\StellarSdk\Model\Payment;
use ZuluCrypto\StellarSdk\Server;

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
                            'callback_data' => self::createRoute('index'),
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

        if (!isset($user->stellar)) {
            return $this->actionIndex();
        }

        $userStellar = $user->stellar;

        if ($stellarServer = new StellarServer()) {
            if (!($stellarServer->accountExists($userStellar->getPublicKey()))) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery(
                        $this->render('alert-account-doesnt-exist'),
                        true
                    )
                    ->build();
            }

            $userSentTransaction = $stellarServer->operationExists(
                $userStellar->getPublicKey(),
                $stellarServer->getDistributorPublicKey(),
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
}
