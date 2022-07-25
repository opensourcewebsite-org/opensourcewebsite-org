<?php

namespace app\modules\bot\controllers\groups;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\models\UserStellar;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\models\StellarServer;

/**
 * Class StellarController
 *
 * @package app\modules\bot\controllers\groups
 */
class StellarController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();

        if ($chat->stellar_status == ChatSetting::STATUS_ON && $chat->stellar_issuer) {
            $isModeSigners = ($chat->stellar_mode == ChatSetting::STELLAR_MODE_SIGNERS);
            $verifiedUsers = [];

            if ($isModeSigners) {
                $signerPublicKeys = [];

                if ($stellarServer = new StellarServer()) {
                    if ($account = $stellarServer->getAccount($chat->stellar_issuer)) {
                        $signers = $account->getSigners();
                        $signerPublicKeys = [];

                        foreach ($signers as $signer) {
                            if (($signer['weight'] > 0) && ($signer['type'] == 'ed25519_public_key')) {
                                $signerPublicKeys[] = $signer['key'];
                            }
                        }

                        $verifiedUsers = $chat->getHumanUsers()
                            ->leftJoin(UserStellar::tableName(), UserStellar::tableName() . '.user_id = ' . User::tableName() .'.user_id')
                            ->andWhere([
                                'not', ['confirmed_at' => null],
                            ])
                            ->andWhere([
                                'in',
                                'public_key',
                                $signerPublicKeys,
                            ])
                            ->all();
                    }
                }
            } else {
                // TODO for verified holders
            }

            return $this->getResponseBuilder()
                ->sendMessage(
                    $this->render('index', [
                        'chat' => $chat,
                        'isModeSigners' => $isModeSigners,
                        'verifiedUsers' => $verifiedUsers,
                    ]),
                    [],
                    [
                        'disablePreview' => true,
                        'disableNotification' => true,
                        'replyToMessageId' => $this->getMessage()->getMessageId(),
                    ]
                )
                ->build();
        }
    }
}
