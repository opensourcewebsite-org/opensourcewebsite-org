<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\filters\ChannelCreatorAccessFilter;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;
use Yii;

/**
 * Class ChannelDeleteController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelDeleteController extends Controller
{
    public function behaviors()
    {
        return [
            'channelCreatorAccess' => [
                'class' => ChannelCreatorAccessFilter::class,
            ],
        ];
    }

    /**
     * @param int $id Chat->id
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionIndex($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $this->getState()->clearInputRoute();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'chat' => $chat,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('confirm', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'YES'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute('view', [
                                'chatId' => $chat->id,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ]
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionConfirm($id = null)
    {
        $chat = Yii::$app->cache->get('chat');

        $chat->delete();

        return $this->run('channel/index');
    }
}
