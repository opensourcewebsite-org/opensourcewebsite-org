<?php
namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\models\AdCategory;
use app\modules\bot\models\UserSetting;
use app\modules\bot\models\AdKeyword;
use app\modules\bot\models\AdsPost;
use app\modules\bot\models\AdsPostSearch;

class MyAdsPostsController extends Controller
{
    public function actionIndex()
    {
        $responseBuilder = ResponseBuilder::fromUpdate($this->getUpdate());

        $responseBuilder->sendMessage($this->render('index'));

        foreach (AdsPost::find()->where(['user_id' => $this->getTelegramUser()->id])->all() as $adsPost) {
            $responseBuilder->sendPhotoOrSendMessage(
                $adsPost->photo_file_id,
                $this->adsPostReply($adsPost),
                $this->adsPostButtons($adsPost)
            );
        }

        return $responseBuilder->build();
    }

    private static function getKeywordsAsString($adsPost)
    {
        $reply = '';

        foreach ($adsPost->getKeywords()->all() as $adKeyword) {
            if (!empty($reply)) {
                $reply .= ' ';
            }

            $reply .= $adKeyword->word;
        }

        return $reply;
    }

    private function adsPostReply($adsPost)
    {
        return $this->render('ads-post', [
            'adsPost' => $adsPost,
            'statusName' => $adsPost->getStatusName(),
            'keywords' => self::getKeywordsAsString($adsPost),
            'categoryName' => AdCategory::findOne($adsPost->category_id)->name,
            'telegramUser' => $this->getTelegramUser(),
        ]);
    }

    private function adsPostButtons($adsPost)
    {
        return [
            [
                [
                    'callback_data' => 'no_reply',
                    'text' => Emoji::EDIT,
                ],
                [
                    'callback_data' => self::createRoute('update', ['adsPostId' => $adsPost->id]),
                    'text' => '🔄',
                ],
            ],
            [
                [
                    'callback_data' => self::createRoute('confirm-delete', ['adsPostId' => $adsPost->id]),
                    'text' => Emoji::DELETE,
                ],
                [
                    'callback_data' => self::createRoute('confirm-status', ['adsPostId' => $adsPost->id]),
                    'text' => Yii::t('bot', ($adsPost->isActive() ? "Отозвать" : "Активировать")),
                ],
            ],
        ];
    }

    public function actionConfirmStatus($adsPostId)
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-status', ['adsPost' => AdsPost::findOne($adsPostId)]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('status', ['adsPostId' => $adsPostId]),
                            'text' => '✅',
                        ],
                    ],
                ],
            )
            ->build();
    }

    public function actionStatus($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        if (isset($adsPost)) {
            if ($adsPost->isActive()) {
                $adsPost->status = AdsPost::STATUS_NOT_ACTIVATED;
            } else {
                $adsPost->status = AdsPost::STATUS_ACTIVATED;
            }

            $adsPost->save();

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->sendPhotoOrEditMessageTextOrSendMessage(
                    $adsPost->photo_file_id,
                    $this->adsPostReply($adsPost),
                    $this->adsPostButtons($adsPost),
                )
                ->build();  
        }
    }

    public function actionPost($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        if (isset($adsPost)) {
            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->sendPhotoOrEditMessageTextOrSendMessage(
                    $adsPost->photo_file_id,
                    $this->adsPostReply($adsPost),
                    $this->adsPostButtons($adsPost)
                )
                ->build();
        }
    }

    public function actionUpdate($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        if (isset($adsPost)) {
            $adsPost->updated_at = time();
            $adsPost->save();

            return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('update', ['adsPost' => $adsPost]),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
                                'text' => Emoji::BACK,
                            ],
                        ],
                    ]
                )
                ->build();
        }
    }

    public function actionConfirmDelete($adsPostId)
    {
        $adsPost = AdsPost::findOne($adsPostId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('confirm-delete'),
                [
                    [
                        [
                            'callback_data' => self::createRoute('post', ['adsPostId' => $adsPostId]),
                            'text' => '❌',
                        ],
                        [
                            'callback_data' => self::createRoute('delete', ['adsPostId' => $adsPostId]),
                            'text' => '✅',
                        ],
                    ],
                ]
            )
            ->build();
    }

    public function actionDelete($adsPostId)
    {
        AdsPost::findOne($adsPostId)->delete();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->deleteMessage()
            ->build();
    }
}
