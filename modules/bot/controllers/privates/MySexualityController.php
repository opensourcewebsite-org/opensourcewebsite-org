<?php

namespace app\modules\bot\controllers\privates;

use app\models\Sexuality;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;

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

        return $this->getResponseBuilder()
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
                $text = $this->render('update'),
                array_merge($sexualityRows, [ $paginationButtons ], [
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
