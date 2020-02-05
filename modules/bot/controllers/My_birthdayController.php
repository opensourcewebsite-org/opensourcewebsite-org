<?php

namespace app\modules\bot\controllers;

/**
 * Class My_birthdayController
 *
 * @package app\modules\bot\controllers
 */
class My_birthdayController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
    	// $botClient = \Yii::$app->botClient->getModel();
     //    $text = \Yii::$app->requestMessage->getText();
    	// $success = $this->validateDate($text, 'd.m.Y');
     //    if ($success)
     //    {
     //        $botClient->setState();
     //    }

        return [
            [
                'type' => 'message',
                'text' => $this->render('index'),
            ]
        ];
    }

    private function validateDate($date, $format)
    {
        $dateObject = \DateTime::createFromFormat($format, $date);
        return $dateObject && $dateObject->format($format) === $date;
    }
}
