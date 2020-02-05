<?php

namespace app\modules\bot\controllers;

/**
 * Class My_ratingController
 *
 * @package app\modules\bot\controllers
 */
class My_ratingController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $params = [
            'active_rating' => 0,
            'overall_rating' => [0, 1000],
            'ranking' => [120, 120],
        ];

        return $this->render('index', $params);
    }
}
