<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Sexuality;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\models\User;
use yii\data\Pagination;

/**
 * Class MySexualityController
 *
 * @package app\modules\bot\controllers\privates
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

        if (!$user->sexuality_id) {
            return $this->actionUpdate();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sexuality' => $user->sexuality->name,
                ]),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
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
        $user = $this->getUser();

        $sexualityQuery = Sexuality::find();
        $pagination = new Pagination([
            'totalCount' => $sexualityQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);
        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('update', [
                'page' => $page,
            ]);
        });
        $sexualities = $sexualityQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();
        $sexualityRows = array_map(function ($sexuality) {
            return [
                [
                    'text' => Yii::t('bot', $sexuality->name),
                    'callback_data' => self::createRoute('index', [
                        'sexualityId' => $sexuality->id,
                    ]),
                ],
            ];
        }, $sexualities);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('update'),
                array_merge($sexualityRows, [$paginationButtons], [
                    [
                        [
                            'callback_data' => ($user->sexuality_id ? self::createRoute() : MyProfileController::createRoute()),
                            'text' => Emoji::BACK,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
