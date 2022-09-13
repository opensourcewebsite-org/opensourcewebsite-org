<?php

namespace app\modules\bot\controllers\privates;

use app\models\Country;
use app\models\UserCitizenship;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use yii\data\Pagination;
use yii\db\StaleObjectException;

use function foo\func;

/**
 * Class MyCitizenshipsController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyCitizenshipsController extends Controller
{
    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->setName(null);

        $query = $this->globalUser->getCitizenships();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $citizenships = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($citizenships) {
            foreach ($citizenships as $citizenship) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'id' => $citizenship->country->id,
                    ]),
                    'text' => $citizenship->country->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => MyProfileController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'text' => Emoji::MENU,
                'callback_data' => MenuController::createRoute(),
            ],
            [
                'callback_data' => self::createRoute('add'),
                'text' => Emoji::ADD,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int|null $id Country->id
     * @param int $page
     * @return array
     */
    public function actionAdd($id = null, $page = 1)
    {
        if ($id) {
            $country = Country::findOne($id);

            if ($country) {
                $citizenship = $this->globalUser
                    ->getCitizenships()
                    ->where([
                        'country_id' => $id,
                    ])
                    ->one() ?? new UserCitizenship();

                $citizenship->setAttributes([
                    'user_id' => $this->globalUser->id,
                    'country_id' => $country->id,
                ]);
                $citizenship->save();

                return $this->actionIndex();
            }
        }

        if ($this->getUpdate()->getMessage()) {
            if ($text = $this->getUpdate()->getMessage()->getText()) {
                if (strlen($text) <= 3) {
                    $country = Country::find()
                        ->orFilterWhere(['like', 'code', $text, false])
                        ->one();
                } else {
                    $country = Country::find()
                        ->orFilterWhere(['like', 'name', $text . '%', false])
                        ->orFilterWhere(['like', 'slug', $text, false])
                        ->one();
                }

                if ($country) {
                    $citizenship = $this->globalUser
                        ->getCitizenships()
                        ->where([
                            'country_id' => $id,
                        ])
                        ->one() ?? new UserCitizenship();

                    $citizenship->setAttributes([
                        'user_id' => $this->globalUser->id,
                        'country_id' => $country->id,
                    ]);
                    $citizenship->save();

                    return $this->actionIndex();
                }
            }
        }

        $this->getState()->setName(self::createRoute('add'));

        $query = Country::find();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $countries = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($countries) {
            foreach ($countries as $country) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('add', [
                        'id' => $country->id,
                    ]),
                    'text' => $country->name,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('add', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => self::createRoute(),
                'text' => Emoji::BACK,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('add'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int|null $id Country->id
     * @return array
     */
    public function actionView($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $country = Country::findOne($id);

        if (!isset($country)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'countryName' => $country->name,
                ]),
                [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute(),
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'text' => Emoji::DELETE,
                            'callback_data' => self::createRoute('delete', [
                                'id' => $id,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($id = null)
    {
        if (!$id) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $citizenship = $this->globalUser
            ->getCitizenships()
            ->where([
                'country_id' => $id,
            ])
            ->one();

        if (!isset($citizenship)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        try {
            $citizenship->delete();
        } catch (StaleObjectException $e) {
        } catch (\Throwable $e) {
        }

        return $this->actionIndex();
    }
}
