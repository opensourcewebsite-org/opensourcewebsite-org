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
    public $firstIteration = true;
    public $userPagesId;
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
        if ($this->firstIteration) {
            $this->userPagesId = [];
        }

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

            foreach ($data->watchlistraw as $page) {
                //Search if the page already exists
                $wikiPage = WikiPage::find()
                    ->where([
                        'ns' => $page->ns,
                        'title' => $page->title,
                        'language_id' => $language->id
                    ])
                    ->one();

                //If the page doesn't exist is created
                if ($wikiPage == null) {
                    $wikiPage = new WikiPage([
                        'ns' => $page->ns,
                        'language_id' => $language->id,
                        'title' => $page->title,
                    ]);

                    if (!$wikiPage->save()) {
                        continue;
                    }
                }

                //The page id is added to the user list
                $this->userPagesId[] = $wikiPage->id;

                //If the page is not yet related to the user, is saved the relation
                $relation = UserWikiPage::find()
                    ->where([
                        'user_id' => $this->user_id,
                        'wiki_page_id' => $wikiPage->id
                    ])
                    ->one();

                if ($relation == null) {
                    $relation = new UserWikiPage([
                        'user_id' => $this->user_id,
                        'wiki_page_id' => $wikiPage->id
                    ]);
                    
                    $relation->save();
                }
            }

            if (isset($data->continue)) {
                $this->firstIteration = false;
                $this->_continue = $data->continue->wrcontinue;
                $this->run();
            } else {
                //Delete all pages of the current language that aren't in the userPagesId list
                $languagePagesId = WikiPage::find()
                    ->select('id')
                    ->where(['language_id' => $language->id])
                    ->column();
                
                UserWikiPage::deleteAll([
                    'and',
                    ['user_id' => $this->user_id],
                    ['not in', 'wiki_page_id', $this->userPagesId],
                    ['in', 'wiki_page_id', $languagePagesId],
                ]);
            }
        }
    }
}
