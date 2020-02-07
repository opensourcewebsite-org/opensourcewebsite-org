<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;

/**
 * Class My_genderController
 *
 * @package app\modules\bot\controllers
 */
class My_genderController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
    	return [
            [
                'type' => 'message',
                'text' => $this->render('index',
                	[
                		'gender' => $this->module->user->gender
                	]),
                'replyMarkup' => new InlineKeyboardMarkup(
                	[
                		[
                			[
                				'callback_data' => 'change_gender',
                				'text' => Yii::t('bot', 'Change Gender')
                			]
                		]
                	]),
            ]
        ];
    }

    public function actionChange()
    {
    	return [
    		[
    			'type' => 'editMessage',
    			'text' => $this->render('index',
    				[
    					'gender' => $this->module->user->gender,
    				]),
    			'replyMarkup' => new InlineKeyboardMarkup(
    				[
    					[
    						[
    							'callback_data' => '/set_gender_back',
    							'text' => Yii::t('bot', 'Back'),
    						],
    						[
    							'callback_data' => '/set_gender_male',
    							'text' => Yii::t('bot', 'Male'),
    						],
    						[
    							'callback_data' => '/set_gender_female',
    							'text' => Yii::t('bot', 'Female'),
    						]
    					]
    				]),
    		],
    		[
    			'type' => 'callback'
    		]
    	];
    }

    public function actionSetMale()
    {
    	$this->module->user->gender = 0;
    	$this->module->user->save();
    	return [
    		[
    			'type' => 'editMessage',
    			'text' => $this->render('index',
    				[
    					'gender' => $this->module->user->gender,
    				]),
    			'replyMarkup' => new InlineKeyboardMarkup(
    				[
                		[
                			[
                				'callback_data' => '/change_gender',
                				'text' => Yii::t('bot', 'Change Gender')
                			]
                		]
    				]),
    		]
    	];
    }

    public function actionSetFemale()
    {
    	$this->module->user->gender = 1;
    	$this->module->user->save();
    	return [
    		[
    			'type' => 'editMessage',
    			'text' => $this->render('index',
    				[
    					'gender' => $this->module->user->gender,
    				]),
    			'replyMarkup' => new InlineKeyboardMarkup(
    				[
                		[
                			[
                				'callback_data' => '/change_gender',
                				'text' => Yii::t('bot', 'Change Gender')
                			]
                		]
    				]),
    		]
    	];
    }

    public function actionBack()
    {
		return [
    		[
    			'type' => 'editMessage',
    			'text' => $this->render('index',
    				[
    					'gender' => $this->module->user->gender,
    				]),
    			'replyMarkup' => new InlineKeyboardMarkup(
    				[
                		[
                			[
                				'callback_data' => '/change_gender',
                				'text' => Yii::t('bot', 'Change Gender')
                			]
                		]
    				]),
    		]
    	];
    }
}
