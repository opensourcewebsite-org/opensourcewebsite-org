<?php
// TODO
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use yii\helpers\ArrayHelper;
use app\models\UaLawmakingVote;
use app\models\UaLawmakingVoting;

/**
 * Class UaLawmakingController
 *
 * @package app\modules\bot\controllers\privates
 */
class UaLawmakingController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!YII_ENV_DEV) {
            return false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $buttons[][] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionViewDemo1()
    {
        $voting = new UaLawmakingVoting();
        $voting->event_id = 7333;
        $voting->name = 'Demo1';
        $voting->for = 300;
        $voting->against = 30;
        $voting->abstain = 20;
        $voting->not_voting = 10;
        $voting->presence = 360;
        $voting->date = '2020-10-01';

        $likeVotes = 4221;
        $dislikeVotes = 42;

        $buttons[] = [
            [
                'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                'callback_data' => self::createRoute('view-demo1'),
            ],
            [
                'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                'callback_data' => self::createRoute('view-demo1'),
            ],
        ];

        $buttons[] = [
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('@bot/views/' . $this::CHANNEL_NAMESPACE . '/' . $this->id . '/show-voting', [
                    'voting' => $voting,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionViewDemo2()
    {
        $voting = new UaLawmakingVoting();
        $voting->event_id = 7333;
        $voting->name = 'Demo2';
        $voting->for = 100;
        $voting->against = 30;
        $voting->abstain = 20;
        $voting->not_voting = 10;
        $voting->presence = 160;
        $voting->date = '2020-10-01';

        $likeVotes = 4221;
        $dislikeVotes = 42;

        $buttons[] = [
            [
                'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                'callback_data' => self::createRoute('view-demo2'),
            ],
            [
                'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                'callback_data' => self::createRoute('view-demo2'),
            ],
        ];

        $buttons[] = [
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('@bot/views/' . $this::CHANNEL_NAMESPACE . '/' . $this->id . '/show-voting', [
                    'voting' => $voting,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    /**
     * @return array
     */
    public function actionSendDemo1()
    {
        if (isset(Yii::$app->params['bot']['ua_lawmaking'])) {
            $config = Yii::$app->params['bot']['ua_lawmaking'];

            $voting = new UaLawmakingVoting();
            $voting->event_id = 7333;
            $voting->name = 'Demo1';
            $voting->for = 300;
            $voting->against = 30;
            $voting->abstain = 20;
            $voting->not_voting = 10;
            $voting->presence = 360;
            $voting->date = '2020-10-01';

            $likeVotes = 4221;
            $dislikeVotes = 42;

            $buttons[] = [
                [
                    'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                    'callback_data' => self::createRoute('view-demo'),
                ],
                [
                    'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                    'callback_data' => self::createRoute('view-demo'),
                ],
            ];

            return $this->getResponseBuilder()
                ->setChatId($config['chat_id'])
                ->sendMessage(
                    $this->render('@bot/views/' . $this::CHANNEL_NAMESPACE . '/' . $this->id . '/show-voting', [
                        'voting' => $voting,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
                ->build();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }

    /**
     * @return array
     */
    public function actionSendDemo2()
    {
        if (isset(Yii::$app->params['bot']['ua_lawmaking'])) {
            $config = Yii::$app->params['bot']['ua_lawmaking'];

            $voting = new UaLawmakingVoting();
            $voting->event_id = 7333;
            $voting->name = 'Demo2';
            $voting->for = 100;
            $voting->against = 30;
            $voting->abstain = 20;
            $voting->not_voting = 10;
            $voting->presence = 160;
            $voting->date = '2020-10-01';

            $likeVotes = 4221;
            $dislikeVotes = 42;

            $buttons[] = [
                [
                    'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                    'callback_data' => self::createRoute('view-demo'),
                ],
                [
                    'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                    'callback_data' => self::createRoute('view-demo'),
                ],
            ];

            return $this->getResponseBuilder()
                ->setChatId($config['chat_id'])
                ->sendMessage(
                    $this->render('@bot/views/' . $this::CHANNEL_NAMESPACE . '/' . $this->id . '/show-voting', [
                        'voting' => $voting,
                    ]),
                    $buttons,
                    [
                        'disablePreview' => true,
                    ]
                )
                ->build();
        }

        return $this->getResponseBuilder()
            ->answerCallbackQuery()
            ->build();
    }
}
