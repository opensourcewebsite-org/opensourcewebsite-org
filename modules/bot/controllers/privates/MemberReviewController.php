<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\MessageWithEntitiesConverter;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatMemberReview;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;

/**
 * Class MemberReviewController
 *
 * @package app\modules\bot\controllers\privates
 */
class MemberReviewController extends Controller
{
    /**
     * @param int $page
     * @param int|null $id ChatMember->id
     * @return array
     */
    public function actionIndex($page = 1, $id = null)
    {
        $chatMember = ChatMember::findOne([
            'id' => $id,
        ]);

        if (!isset($chatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->setName(null);

        $query = ChatMemberReview::find()
            ->where([
                'member_id' => $chatMember->id,
            ])
            ->active()
            ->orderByRank();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 1,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $reviews = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chatMember) {
            return self::createRoute('index', [
                'page' => $page,
                'id' => $chatMember->id,
            ]);
        });

        $buttons = [];

        if ($reviews) {
            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        } else {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $review = $reviews[0];

        $user = $this->getTelegramUser();

        $buttons[] = [
            [
                'callback_data' => MemberController::createRoute('id', [
                    'id' => $chatMember->id,
                ]),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
            [
                'callback_data' => MemberController::createRoute('my-review', [
                    'id' => $chatMember->id,
                ]),
                'text' => Emoji::EDIT,
                'visible' => $user->id == $review->getUserId(),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'user' => $chatMember->user,
                    'authorUser' => $review->user,
                    'chat' => $chatMember->chat,
                    'chatMember' => $chatMember,
                    'review' => $review,
                ]),
                $buttons,
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
