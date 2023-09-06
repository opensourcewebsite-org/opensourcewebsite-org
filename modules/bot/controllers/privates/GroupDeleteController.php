<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\filters\GroupCreatorAccessFilter;
use Yii;

/**
 * Class GroupDeleteController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupDeleteController extends Controller
{
    public function behaviors()
    {
        return [
            'groupCreatorAccess' => [
                'class' => GroupCreatorAccessFilter::class,
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
                            'callback_data' => GroupController::createRoute('view', [
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

        return $this->run('group/index');
    }
}
