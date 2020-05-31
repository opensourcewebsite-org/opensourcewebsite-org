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

class MyAdsSearchesController extends Controller
{
    public function actionIndex()
    {
        $responseBuilder = ResponseBuilder::fromUpdate($this->getUpdate());

        $responseBuilder->sendMessage($this->render('index'));

        $buttons = [];

        foreach (AdsPostSearch::find()->where([
            'user_id' => $this->getTelegramUser()->id,
        ])->all() as $adsPostSearch) {
            $responseBuilder->sendMessage(
                $this->render('ads-post-search', [
                    'adsPostSearch' => $adsPostSearch,
                    'keywords' => self::getKeywordsAsString($adsPostSearch),
                ]),
                [
                    [
                        [
                            'callback_data' => 'no_reply',
                            'text' => Emoji::EDIT,
                        ],
                        [
                            'callback_data' => 'no_reply',
                            'text' => '🔄',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('delete', ['adsPostSearchId' => $adsPostSearch->id]),
                            'text' => Emoji::DELETE,
                        ],
                        [
                            'callback_data' => 'no_reply',
                            'text' => Yii::t('bot', $adsPostSearch->status == AdsPostSearch::STATUS_ACTIVATED ? 'Отозвать' : 'Активировать'), 
                        ],
                    ],
                ]
            );
        }

        return $responseBuilder->build();
    }

    public function actionDelete($adsPostSearchId) {
        $adsPostSearch = AdsPostSearch::findOne($adsPostSearchId);

        Yii::warning("ID: " . $adsPostSearchId);

        // $adsPostSearch->unlinkAll('keywords', true);
        // $adsPostSearch->delete();

        Yii::warning('deleting...');

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('delete')
            )
            ->build();

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->deleteMessage()
            ->build();
    }

    private static function getKeywordsAsString($adsPostSearch)
    {
        $reply = '';

        foreach ($adsPostSearch->getKeywords()->all() as $adKeyword) {
            if (!empty($reply)) {
                $reply .= ' ';
            }

            $reply .= $adKeyword->word;
        }

        return $reply;
    }
}
