<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\components\Controller;
use app\modules\bot\models\User as BotUser;
use app\models\forms\MergeAccountsForm;
use yii\base\Exception;

class MergeAccountsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new MergeAccountsForm();

        if (Yii::$app->request->isPost && ($postData = Yii::$app->request->post())) {
            if ($model->load($postData) && $model->login()) {
                if ($this->mergeAccounts($this->user, $model->user)) {
                    return $this->render('done', [
                        'user' => $this->user,
                    ]);
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'user' => $this->user,
        ]);
    }

    private function mergeAccounts($user, $userToMerge)
    {
        \app\models\CompanyUser::updateAll([
            'user_id' => $user->id,
        ], "user_id = {$userToMerge->id}");

        \app\models\AdSearch::updateAll([
            'user_id' => $user->id,
            'status' => \app\models\AdSearch::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        \app\models\AdSearchResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        \app\models\AdOffer::updateAll([
            'user_id' => $user->id,
            'status' => \app\models\AdOffer::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        \app\models\AdOfferResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        \app\models\Resume::updateAll([
            'user_id' => $user->id,
            'status' => \app\models\Resume::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        \app\models\JobResumeResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        \app\models\Vacancy::updateAll([
            'user_id' => $user->id,
            'status' => \app\models\Vacancy::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        \app\models\JobVacancyResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        \app\models\CurrencyExchangeOrder::updateAll([
            'user_id' => $user->id,
            'status' => \app\models\CurrencyExchangeOrder::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        \app\models\CurrencyExchangeOrderResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        \app\modules\comment\models\MoqupComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\Issue::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\modules\comment\models\IssueComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\Moqup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\SupportGroup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\SupportGroupBotClient::updateAll(['provider_bot_user_id' => $user->id], "provider_bot_user_id = {$userToMerge->id}");
        \app\models\SupportGroupMember::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\UserIssueVote::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        \app\models\UserMoqupFollow::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        // move all non-user contacts
        \app\models\Contact::updateAll(
            [
                'user_id' => $user->id,
            ],
            [
                'user_id' => $userToMerge->id,
                'link_user_id' => null,
            ]
        );
        // move unique user contacts, delete identical
        foreach ($userToMerge->contacts as $contact) {
            if ($contact->getLinkUserId()) {
                $contactExists = \app\models\Contact::find()
                    ->where([
                        'user_id' => $user->id,
                        'link_user_id' => $contact->getLinkUserId(),
                    ])
                    ->exists();

                if (!$contactExists && ($contact->getLinkUserId() != $user->id)) {
                    $contact->setUserId($user->id);
                    $contact->save();
                } else {
                    $contact->delete();
                }
            }
        }
        // move unique counter contacts, delete identical
        foreach ($userToMerge->counterContacts as $contact) {
            $contactExists = \app\models\Contact::find()
                ->where([
                    'user_id' => $contact->getUserId(),
                    'link_user_id' => $user->id,
                ])
                ->exists();

            if (!$contactExists && ($contact->getUserId() != $user->id)) {
                $contact->setLinkUserId($user->id);
                $contact->save();
            } else {
                $contact->delete();
            }
        }
        // move unique debt redistributions, delete identical
        foreach ($userToMerge->debtRedistributions as $debtRedistribution) {
            $debtRedistributionExists = \app\models\DebtRedistribution::find()
                ->where([
                    'user_id' => $user->id,
                    'link_user_id' => $debtRedistribution->getLinkUserId(),
                    'currency_id' => $debtRedistribution->getCurrencyId(),
                ])
                ->exists();

            if (!$debtRedistributionExists && ($debtRedistribution->getLinkUserId() != $user->id)) {
                $debtRedistribution->setUserId($user->id);
                $debtRedistribution->save();
            } else {
                $debtRedistribution->delete();
            }
        }
        // delete counter debt redistributions
        \app\models\DebtRedistribution::deleteAll("link_user_id = {$userToMerge->id}");
        // move unique setting value votes, delete identical
        foreach ($userToMerge->settingValueVotes as $settingValueVote) {
            $settingValueVoteExists = \app\models\SettingValueVote::find()
                ->where([
                    'user_id' => $user->id,
                    'setting_value_id' => $settingValueVote->getSettingValueId(),
                    'setting_id' => $settingValueVote->getSettingId(),
                ])
                ->exists();

            if (!$settingValueVoteExists) {
                $settingValueVote->setUserId($user->id);
                $settingValueVote->save();
            } else {
                $settingValueVote->delete();
            }
        }
        // move user ratings
        if ($userToMerge->ratings) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                \app\models\Rating::updateAll(
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'user_id' => $userToMerge->id,
                    ]
                );

                $user->updateRating();
                $userToMerge->updateRating();

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                return false;
            }
        }

        if ($userToMerge->userStellar) {
            if (!$user->userStellar) {
                \app\models\UserStellar::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
            } else {
                \app\models\UserStellar::deleteAll("user_id = {$userToMerge->id}");
            }
        }

        if ($userToMerge->depositDebts || $userToMerge->creditDebts) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // delete debts between this users
                \app\models\Debt::deleteAll([
                    'from_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                    'to_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                ]);
                // delete debt balances between this users
                \app\models\DebtBalance::deleteAll([
                    'from_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                    'to_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                ]);

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                return false;
            }
            // refresh query result
            unset($userToMerge->depositDebts);
            unset($userToMerge->creditDebts);

            try {
                // move deposit debts
                foreach ($userToMerge->depositDebts as $debt) {
                    $debt->to_user_id = $user->id;

                    if ($debt->created_by == $userToMerge->id) {
                        $debt->created_by = $user->id;
                    }

                    if ($debt->updated_by == $userToMerge->id) {
                        $debt->updated_by = $user->id;
                    }

                    $debt->save(false);
                }
                // move credit debts
                foreach ($userToMerge->creditDebts as $debt) {
                    $debt->from_user_id = $user->id;

                    if ($debt->created_by == $userToMerge->id) {
                        $debt->created_by = $user->id;
                    }

                    if ($debt->updated_by == $userToMerge->id) {
                        $debt->updated_by = $user->id;
                    }

                    $debt->save(false);
                }
            } catch (Exception $e) {
                return false;
            }
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            \app\models\UserLanguage::deleteAll("user_id = {$userToMerge->id}");
            \app\models\UserCitizenship::deleteAll("user_id = {$userToMerge->id}");

            if ($userToMerge->botUser) {
                if (!$user->botUser) {
                    BotUser::updateAll(['user_id' => $user->id], ['user_id' => $userToMerge->id]);
                } else {
                    BotUser::updateAll(['user_id' => null], ['user_id' => $userToMerge->id]);
                }
            }

            if ($userToMerge->userEmail) {
                if (!$user->userEmail) {
                    \app\models\UserEmail::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
                } else {
                    \app\models\UserEmail::deleteAll("user_id = {$userToMerge->id}");
                }
            }

            $userToMerge->delete();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            return false;
        }

        return true;
    }
}
