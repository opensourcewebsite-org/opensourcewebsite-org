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
        if (!$this->globalUser->sexuality_id) {
            return $this->actionSet();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'sexuality' => $this->globalUser->sexuality,
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
                            'callback_data' => self::createRoute('set'),
                            'text' => Emoji::EDIT,
                        ],
                    ],
                ]
            )
            ->build();
    }

    /**
     * @param int|null $id Sexuality->id
     * @param int $page
     * @return array
     */
    public function actionSet($id = null, $page = 1)
    {
        if ($id) {
            $sexuality = Sexuality::findOne($id);

            if ($sexuality) {
                $this->globalUser->sexuality_id = $sexuality->id;
                $this->globalUser->save();

                return $this->actionIndex();
            }
        }

        $this->getState()->setName(null);

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

        $sexualities = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($sexualities) {
            foreach ($sexualities as $sexuality) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('set', [
                        'id' => $sexuality->id,
                    ]),
                    'text' => Yii::t('bot', $sexuality->name),
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('set', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => ($this->globalUser->sexuality_id ? self::createRoute() : MyProfileController::createRoute()),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('set'),
                $buttons
            )
            ->build();
    }
}
