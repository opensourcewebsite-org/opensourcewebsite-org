<?php

namespace app\modules\bot\controllers\privates;

use app\models\Country;
use app\models\UserCitizenship;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\Controller;
use yii\data\Pagination;
use yii\db\StaleObjectException;
use function foo\func;

/**
 * Class MyCitizenshipController
 *
 * @package app\modules\bot\controllers
 */
class MyCitizenshipController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $citizenshipButtons = PaginationButtons::buildFromQuery(
            $this->getUser()->getCitizenships(),
            function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            },
            function ($citizenship) {
                return [
                    'text' => $citizenship->country->name,
                    'callback_data' => self::createRoute('show', [
                        'countryId' => $citizenship->country->id,
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                array_merge($citizenshipButtons, [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'text' => Emoji::MENU,
                            'callback_data' => MenuController::createRoute(),
                        ],
                        [
                            'callback_data' => self::createRoute('create-country'),
                            'text' => Emoji::ADD,
                        ],
                    ],
                ])
            )
            ->build();
    }

    public function actionCreateCountry($page = 1)
    {
        $countryButtons = PaginationButtons::buildFromQuery(
            Country::find(),
            function ($page) {
                return self::createRoute('create-country', [
                    'page' => $page,
                ]);
            },
            function (Country $country) {
                return [
                    'text' => $country->name,
                    'callback_data' => self::createRoute('create', [
                        'countryId' => $country->id,
                    ]),
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('create-country'),
                array_merge($countryButtons, [
                    [
                        [
                            'text' => Emoji::BACK,
                            'callback_data' => self::createRoute()
                        ]
                    ]
                ])
            )
            ->build();
    }

    public function actionCreate($countryId)
    {
        $country = Country::findOne($countryId);
        if (!isset($country)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        $citizenship = $this->getUser()->getCitizenships()->where([ 'country_id' => $countryId ])->one()
            ?? new UserCitizenship();
        $citizenship->setAttributes([
            'user_id' => $this->getUser()->id,
            'country_id' => $countryId,
        ]);
        $citizenship->save();

        return $this->actionIndex();
    }

    public function actionShow($countryId)
    {
        $country = Country::findOne($countryId);
        if (!isset($country)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->answerCallbackQuery()
                ->build();
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('show', [
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
                                'countryId' => $countryId,
                            ]),
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($countryId)
    {
        $citizenship = $this->getUser()->getCitizenships()->where([ 'country_id' => $countryId ])->one();
        if (!isset($citizenship)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
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
