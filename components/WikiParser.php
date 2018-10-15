<?php

namespace app\components;

use app\models\WikiPage;
use yii\base\BaseObject;
use app\models\UserWikiPage;
use app\models\UserWikiToken;
use yii\web\ServerErrorHttpException;

class WikiParser extends BaseObject
{

    public $user_id;
    public $language_id;
    public $token;
    private $_continue;

    public function init()
    {
        if (!$this->token) {
            if (!$token = UserWikiToken::findOne([
                    'user_id' => $this->user_id,
                    'language_id' => $this->language_id,
                ])
            ) {
                throw new ServerErrorHttpException('Token not found');
            }

            $this->token = $token;
        }
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function run($justValidateUser = false)
    {
        $token = $this->token;

        $language = $token->language;

        $url = "https://{$language->code}.wikipedia.org/w/api.php?action=query&format=json&list=watchlistraw&wrtoken={$token->token}"
            . "&wrnamespace=0|2|4|6|8|10|12|14";

        if ($username = $token->wiki_username) {
            $url .= "&wrowner=" . urlencode($username);
        }

        if ($this->_continue) {
            $url .= "&wrcontinue={$this->_continue}";
        }

        if ($justValidateUser) {
            $url .= '&wrlimit=1';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($data = json_decode($result)) {
            if (isset($data->error)) {
                if ($data->error->info == 'Incorrect watchlist token provided. Please set a correct token in [[Special:Preferences]].') {
                    $error = "Incorrect watchlist token provided. <a href='https://{$language->code}.wikipedia.org/wiki/Special:ResetTokens' target='_blank'>Please set a correct token</a>";
                } else {
                    $error = $data->error->info;
                }
                throw new ServerErrorHttpException($error);
            } elseif ($justValidateUser) {
                return true;
            }

            /** @var UserWikiPage $page */
            if (!$this->_continue) {
                foreach (UserWikiPage::find()
                    ->joinWith('wikiPage')
                    ->where(['user_id' => $this->user_id, 'wiki_page.language_id' => $language->id])
                    ->each() as $userPage) {
                    $userPage->delete();
                }
            }

            foreach ($data->watchlistraw as $page) {
                $model = new WikiPage([
                    'ns' => $page->ns,
                    'language_id' => $language->id,
                    'title' => $page->title,
                ]);

                if ($model->validate()) {
                    if (!WikiPage::find()->where(['title' => $model->title, 'language_id' => $language->id])->exists()) {
                        $model->detachBehavior('timestamp');
                        $model->save(false);
                        $id = $model->id;
                    } else {
                        $id = WikiPage::find()
                            ->select('id')
                            ->where(['title' => $model->title, 'language_id' => $language->id])
                            ->scalar();
                    }

                    if (!UserWikiPage::find()->where(['user_id' => $this->user_id, 'wiki_page_id' => $id])->exists()) {
                        $link = new UserWikiPage([
                            'user_id' => $this->user_id,
                            'wiki_page_id' => $id,
                        ]);
                        $link->save();
                    }
                }
            }

            if (isset($data->continue)) {
                $this->_continue = $data->continue->wrcontinue;
                $this->run();
            }
        }
    }
}
