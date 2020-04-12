<?php

namespace app\modules\bot\controllers\privates;

use app\models\Sexuality;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\models\User;
use app\modules\bot\components\Controller;
use yii\data\Pagination;

/**
 * Class MySexualityController
 *
 * @package app\modules\bot\controllers
 */
class MySexualityController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($sexualityId = null)
    {
        $user = $this->getUser();

        if (isset($sexualityId)) {
            $sexuality = Sexuality::findOne($sexualityId);
            if (isset($sexuality)) {
                $user->sexuality_id = $sexuality->id;
                $user->save();
            }
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sexuality' => isset($user->sexuality) ? $user->sexuality->name : null,
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => self::createRoute('update'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionUpdate($page = 1)
    {
        $sexualityButtons = PaginationButtons::buildFromQuery(
            Sexuality::find(),
            function ($page) {
                return self::createRoute('update', [
                    'page' => $page,
                ]);
            },
            function (Sexuality $sexuality) {
                return [
                    'text' => Yii::t('bot', $sexuality->name),
                    'callback_data' => self::createRoute('index', [
                        'sexualityId' => $sexuality->id,
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $text = $this->render('update'),
                array_merge($sexualityButtons, [
                    [
                        [
                            'callback_data' => self::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
