<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\actions\privates\wordlist\WordlistComponent;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatFaqQuestion;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupGuestFaqController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupGuestFaqController extends Controller
{
    public function actions()
    {
        return array_merge(
            parent::actions(),
            Yii::createObject([
                'class' => WordlistComponent::class,
                'wordModelClass' => ChatFaqQuestion::class,
                'options' => [
                    'actions' => [
                        'select' => false,
                        'insert' => false,
                        'update' => false,
                        'delete' => false,
                    ],
                    'listBackRoute' => [
                        'controller' => 'app\modules\bot\controllers\privates\GroupGuestController',
                        'action' => 'view',
                    ],
                ]
            ])->actions()
        );
    }

    /**
     * @param int $id Chat->id
     * @return array
     */
    public function actionIndex($id = null)
    {
        $chat = Chat::findOne($id);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!$chat->hasUsername() && !$chatMember) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        return $this->run('group-guest/view', [
            'id' => $chat->id,
        ]);
    }
}
