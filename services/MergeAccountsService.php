<?php

namespace app\services;

use app\components\Controller;
use app\models\AdOffer;
use app\models\AdOfferResponse;
use app\models\AdSearch;
use app\models\AdSearchResponse;
use app\models\CompanyUser;
use app\models\Contact;
use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderResponse;
use app\models\Debt;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use app\models\Issue;
use app\models\JobResumeResponse;
use app\models\JobVacancyResponse;
use app\models\Moqup;
use app\models\Rating;
use app\models\Resume;
use app\models\SettingValueVote;
use app\models\SupportGroup;
use app\models\SupportGroupBotClient;
use app\models\SupportGroupMember;
use app\models\UserCitizenship;
use app\models\UserEmail;
use app\models\UserIssueVote;
use app\models\UserLanguage;
use app\models\UserMoqupFollow;
use app\models\Vacancy;
use app\models\Wallet;
use app\models\WalletTransaction;
use app\modules\bot\models\User as BotUser;
use app\modules\comment\models\IssueComment;
use app\modules\comment\models\MoqupComment;
use Yii;
use yii\base\Exception;

class MergeAccountsService
{
    public function mergeAccounts($user, $userToMerge)
    {
        CompanyUser::updateAll([
            'user_id' => $user->id,
        ], "user_id = {$userToMerge->id}");

        AdSearch::updateAll([
            'user_id' => $user->id,
            'status' => AdSearch::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        AdSearchResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        AdOffer::updateAll([
            'user_id' => $user->id,
            'status' => AdOffer::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        AdOfferResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        Resume::updateAll([
            'user_id' => $user->id,
            'status' => Resume::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        JobResumeResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        Vacancy::updateAll([
            'user_id' => $user->id,
            'status' => Vacancy::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        JobVacancyResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        CurrencyExchangeOrder::updateAll([
            'user_id' => $user->id,
            'status' => CurrencyExchangeOrder::STATUS_OFF,
        ], "user_id = {$userToMerge->id}");
        CurrencyExchangeOrderResponse::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");

        MoqupComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        Issue::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        IssueComment::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        Moqup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        SupportGroup::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        SupportGroupBotClient::updateAll(['provider_bot_user_id' => $user->id], "provider_bot_user_id = {$userToMerge->id}");
        SupportGroupMember::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        UserIssueVote::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        UserMoqupFollow::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
        // move all non-user contacts
        Contact::updateAll(
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
                $contactExists = Contact::find()
                    ->where([
                        'user_id' => $user->id,
                        'link_user_id' => $contact->getLinkUserId(),
                    ])
                    ->exists();

                if (!$contactExists && ($contact->getLinkUserId() != $user->id)) {
                    $contact->setUserId($user->id);
                    $contact->save(false);
                } else {
                    $contact->delete();
                }
            }
        }
        // move unique counter contacts, delete identical
        foreach ($userToMerge->counterContacts as $contact) {
            $contactExists = Contact::find()
                ->where([
                    'user_id' => $contact->getUserId(),
                    'link_user_id' => $user->id,
                ])
                ->exists();

            if (!$contactExists && ($contact->getUserId() != $user->id)) {
                $contact->setLinkUserId($user->id);
                $contact->save(false);
            } else {
                $contact->delete();
            }
        }
        // move unique debt redistributions, delete identical
        foreach ($userToMerge->debtRedistributions as $debtRedistribution) {
            $debtRedistributionExists = DebtRedistribution::find()
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
        DebtRedistribution::deleteAll("link_user_id = {$userToMerge->id}");
        // move unique setting value votes, delete identical
        foreach ($userToMerge->settingValueVotes as $settingValueVote) {
            $settingValueVoteExists = SettingValueVote::find()
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
                Rating::updateAll(
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

        if ($userToMerge->depositDebts || $userToMerge->creditDebts) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // delete debts between this users
                Debt::deleteAll([
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
                DebtBalance::deleteAll([
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

        // TODO Wallet
        if ($userToMerge->wallets) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // delete empty Wallets
                Wallet::deleteAll([
                    'user_id' => $userToMerge->id,
                    'amount' => 0,
                ]);
                // delete WalletTransactions between this users
                WalletTransaction::deleteAll([
                    'from_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                    'to_user_id' => [
                        $user->id,
                        $userToMerge->id,
                    ],
                ]);

                WalletTransaction::updateAll(['from_user_id' => $user->id], "from_user_id = {$userToMerge->id}");

                WalletTransaction::updateAll(['to_user_id' => $user->id], "to_user_id = {$userToMerge->id}");

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                return false;
            }
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            UserLanguage::deleteAll("user_id = {$userToMerge->id}");
            UserCitizenship::deleteAll("user_id = {$userToMerge->id}");

            if ($userToMerge->botUser) {
                if (!$user->botUser) {
                    BotUser::updateAll(['user_id' => $user->id], ['user_id' => $userToMerge->id]);
                } else {
                    BotUser::updateAll(['user_id' => null], ['user_id' => $userToMerge->id]);
                }
            }

            if ($userToMerge->userEmail) {
                if (!$user->userEmail) {
                    UserEmail::updateAll(['user_id' => $user->id], "user_id = {$userToMerge->id}");
                } else {
                    UserEmail::deleteAll("user_id = {$userToMerge->id}");
                }
            }

            if (!$user->username && $userToMerge->username) {
                $user->username = $userToMerge->username;
                $user->save(false);
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
