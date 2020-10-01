<?php

namespace  app\modules\bot\controllers\channels;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\models\UaLawmakingVote;
use app\models\UaLawmakingVoting;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class UaLawmakingController
 *
 * @package app\modules\bot\controllers\channels
 */
class UaLawmakingController extends Controller
{
    const LIKE_MESSAGE = '+';
    const DISLIKE_MESSAGE = '-';

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (isset(Yii::$app->params['bot']['ua_lawmaking'])) {
            $config = Yii::$app->params['bot']['ua_lawmaking'];

            if ((isset(Yii::$app->params['bot']['ua_lawmaking'])
                && Yii::$app->params['bot']['ua_lawmaking']['chat_id'] == $this->getTelegramChat()->getChatId())) {
                return parent::beforeAction($action);
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index'),
                [],
                [
                    'disablePreview' => true,
                ]
            )
            ->send();

        return [];
    }

    /**
     * @param int $id UaLawmakingVoting
     *
     * @return array
     */
    public function actionShowVoting($id = null)
    {
        if (!$id) {
            return [];
        }

        $voting = UaLawmakingVoting::find()
            ->where([
                'id' => $id,
                'sent_at' => null,
            ])
            ->one();

        if (!$voting) {
            return [];
        }

        $response = $this->prepareResponseBuilder($voting)->send();

        if ($response) {
            $voting->message_id = $response->getMessageId();
            $voting->sent_at = time();
            $voting->save();
        }

        return [];
    }

    /**
     * https://core.telegram.org/bots/faq#broadcasting-to-users
     *
     * @return array
     */
    public function actionShowNewVoting()
    {
        $voting = UaLawmakingVoting::find()
            ->where([
                'sent_at' => null,
            ])
            ->orderBy([
                'event_id' => SORT_ASC,
            ])
            ->one();

        if (!$voting) {
            return [];
        }

        $response = $this->prepareResponseBuilder($voting)->send();

        if ($response) {
            $voting->message_id = $response->getMessageId();
            $voting->sent_at = time();
            $voting->save();
        }

        return [];
    }

    /**
     * https://core.telegram.org/bots/faq#broadcasting-to-users
     *
     * @return array
     */
    public function actionShowNewVotings()
    {
        $votings = UaLawmakingVoting::find()
            ->where([
                'sent_at' => null,
            ])
            ->orderBy([
                'event_id' => SORT_ASC,
            ])
            ->all();

        if (!$votings) {
            return [];
        }

        foreach ($votings as $voting) {
            $response = $this->prepareResponseBuilder($voting)->send();

            if ($response) {
                $voting->message_id = $response->getMessageId();
                $voting->sent_at = time();
                $voting->save();
                // sleep 60 second for telegram anti ddos
                sleep(60);
            }
        }

        return [];
    }

    /**
     * @param int $id UaLawmakingVoting
     *
     * @return array
     */
    public function actionLike($id = null)
    {
        if ($this->getUpdate()->getCallbackQuery()) {
            if (!$id) {
                return [];
            }

            $voting = UaLawmakingVoting::find()
                ->where([
                    'id' => $id,
                ])
                ->one();

            if (!$voting) {
                return [];
            }

            if ($this->addOrChangeVote($voting, UaLawmakingVote::VOTE_LIKE)) {
                return $this->prepareResponseBuilder($voting)
                        ->build();
            } else {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }
        }

        return [];
    }

    /**
     * @param int $id UaLawmakingVoting
     *
     * @return array
     */
    public function actionDislike($id = null)
    {
        if ($this->getUpdate()->getCallbackQuery()) {
            if (!$id) {
                return [];
            }

            $voting = UaLawmakingVoting::find()
                ->where([
                    'id' => $id,
                ])
                ->one();

            if (!$voting) {
                return [];
            }

            if ($this->addOrChangeVote($voting, UaLawmakingVote::VOTE_DISLIKE)) {
                return $this->prepareResponseBuilder($voting)
                        ->build();
            } else {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }
        }

        return [];
    }

    /**
     * @param UaLawmakingVoting $voting
     * @param int $estimate
     *
     * @return array
     */
    private function addOrChangeVote(UaLawmakingVoting &$voting, int $estimate)
    {
        $user = $this->getTelegramUser();

        $vote = UaLawmakingVote::find()
            ->where([
                'message_id' => $voting->message_id,
                'provider_voter_id' => $user->provider_user_id,
            ])
            ->one();

        if (!isset($vote)) {
            $vote = new UaLawmakingVote();
            $vote->message_id = $voting->message_id;
            $vote->provider_voter_id = $user->provider_user_id;
            $vote->vote = $estimate;
            $vote->save();
        } else {
            if ($vote->vote != $estimate) {
                $vote->vote = $estimate;
                $vote->save();
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @param UaLawmakingVoting $voting
     *
     * @return array
     */
    private function prepareResponseBuilder(UaLawmakingVoting &$voting)
    {
        $likeVotes = UaLawmakingVote::find()
            ->where([
                'message_id' => $voting->message_id,
                'vote' => UaLawmakingVote::VOTE_LIKE,
            ])
            ->count();

        $dislikeVotes = UaLawmakingVote::find()
            ->where([
                'message_id' => $voting->message_id,
                'vote' => UaLawmakingVote::VOTE_DISLIKE,
            ])
            ->count();

        $buttons[] = [
            [
                'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                'callback_data' => self::createRoute('like', [
                    'id' => $voting->id,
                ]),
            ],
            [
                'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                'callback_data' => self::createRoute('dislike', [
                    'id' => $voting->id,
                ]),
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('show-voting', [
                    'voting' => $voting,
                ]),
                $buttons
            );
    }
}
