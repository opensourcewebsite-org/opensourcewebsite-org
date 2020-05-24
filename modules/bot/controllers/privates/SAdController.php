<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\modules\bot\components\Controller;

/**
 * Class SAdController
 *
 * @package app\modules\bot\controllers
 */
class SAdController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => '📥 ' . 'Купить',
                        ],
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => 'Продать' . ' 📤',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => '📥 ' . 'Аренда',
                        ],
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => 'Аренда'  . ' 📤',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => '📥 ' . 'Услуги',
                        ],
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => 'Услуги' . ' 📤',
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => '👼 ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribution'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ServicesController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => '📱',
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return string
     */
    public function action1()
	{
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('1'),
                [
                    [
                        [
                            'callback_data' => '/s_ad__2',
                            'text' => 'Название 1',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad__2',
                            'text' => '❌ ' . 'Название 2',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => '<',
                        ],
                        [
                            'callback_data' => '/s_ad',
                            'text' => '1/3',
                        ],
                        [
                            'callback_data' => '/s_ad',
                            'text' => '>',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => '🔙',
                        ],
                        [
                            'callback_data' => '/menu',
                            'text' => '📱',
                        ],
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => '➕',
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return string
     */
    public function action2()
	{
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('2'),
                [
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Status: ON',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => '🙋‍♂️ 3',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad__1',
                            'text' => '🔙',
                        ],
                        [
                            'callback_data' => '/menu',
                            'text' => '📱',
                        ],
                        [
                            'callback_data' => '/s_ad__3',
                            'text' => '✏️',
                        ],
                        [
                            'callback_data' => '/s_ad__2',
                            'text' => '🗑',
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return string
     */
    public function action3()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('2'),
                [
                    [
                        [
                            'callback_data' => '/s_ad__4',
                            'text' => 'Название',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Описание',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Цена',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Ключевые слова',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Местонахождение',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad',
                            'text' => 'Доставка',
                        ],
                    ],
                    [
                        [
                            'callback_data' => '/s_ad__2',
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @return string
     */
    public function action4()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('1'),
                [
                    [
                        [
                            'callback_data' => '/s_ad__3',
                            'text' => '🔙',
                        ],
                    ],
                ]
            )
            ->build();
    }
}
