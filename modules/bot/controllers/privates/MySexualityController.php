<?php

namespace app\modules\bot\controllers\privates;

use app\models\Sexuality;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use Yii;
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
    public function actionIndex()
    {
        $this->getState()->setName(null);

        $globalUser = $this->getUser();

        if (!$globalUser->sexuality_id) {
            return $this->actionSelect();
        }

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sexuality' => $globalUser->sexuality->name,
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
                            'callback_data' => self::createRoute('list'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionList($page = 1)
    {
        $globalUser = $this->getUser();

        $query = Sexuality::find();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
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

        $sexualities = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($sexualities) {
            foreach ($sexualities as $sexuality) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('select', [
                        'id' => $sexuality->id,
                    ]),
                    'text' => Yii::t('bot', $sexuality->name),
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ($globalUser->sexuality_id ? self::createRoute() : MyProfileController::createRoute()),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('list'),
                $buttons
            )
            ->build();
    }

    public function actionSelect($id = null)
    {
        $globalUser = $this->getUser();

        if (!$id) {
            return $this->actionList();
        }

        $sexuality = Sexuality::findOne($id);

        if ($sexuality) {
            $globalUser->sexuality_id = $sexuality->id;
            $globalUser->save();
        }

        return $this->actionIndex();
    }
}
