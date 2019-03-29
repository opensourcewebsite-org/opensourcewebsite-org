<?php

namespace app\components;

use app\models\UserWikiPage;
use app\models\UserWikiToken;
use app\models\WikiPage;
use yii\base\BaseObject;
use yii\web\ServerErrorHttpException;
use yii\httpclient\Client;

/**
 * Class WikipediaParser
 * @package app\components
 *
 * @property int $user_id
 * @property int $language_id
 * @property \app\models\WikiLanguage $language
 * @property \app\models\UserWikiToken $token
 * @property bool $firstIteration
 * @property array $userPagesId
 * @property string $_continue
 */
class WikipediaParser extends BaseObject
{

    public $user_id;
    public $language_id;
    public $language;
    public $token;
    public $firstIteration = true;
    public $userPagesId;
    private $_continue;

    public function init()
    {
        if (!$this->token) {
            if (!$token = UserWikiToken::findOne([
                'user_id'     => $this->user_id,
                'language_id' => $this->language_id,
            ])
            ) {
                throw new ServerErrorHttpException('Token not found');
            }

            $this->token = $token;
        }
    }

    /**
     * @param bool $justValidateUser
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function run($justValidateUser = false)
    {

        if ($this->firstIteration) {
            $this->userPagesId = [];
        }

        $token = $this->token;

        $this->language = $token->language;

        $client = new Client([
            'baseUrl' => "https://{$this->language->code}.wikipedia.org/w/",
        ]);

        $params = [
            'action'      => 'query',
            'format'      => 'json',
            'list'        => 'watchlistraw',
            'wrtoken'     => $token->token,
            'wrnamespace' => '0|2|4|6|8|10|12|14'
        ];

        // TODO check it out ? Is this condition needed?
        if ($username = $token->wiki_username) {
            $params['wrowner'] = urlencode($username);
        }

        if ($this->_continue) {
            $params['wrcontinue'] = $this->_continue;
        }

        if ($justValidateUser) {
            $params['wrlimit'] = 1;
        }

        $response = $client->get('api.php', $params)->send();

        if (isset($response)) {
            $data = $response->data;

            if (isset($data['error']['code'])) {

                if (!$justValidateUser) {

                    // User changed his token or username
                    $token->status = 1;
                    return $token->save(false);
                }

                $error = "Incorrect watchlist token or username provided.";
                $error .= " <a href='https://{$this->language->code}.wikipedia.org/wiki/Special:ResetTokens' target='_blank'>";
                $error .= " Please set a correct token";
                $error .= "</a>";

                throw new ServerErrorHttpException($error);
            }

            if ($justValidateUser) {
                return true;
            }

            if (isset($data['watchlistraw']) && is_array($data['watchlistraw'])) {

                foreach ($data['watchlistraw'] as $page) {

                    //Search if the page already exists
                    $wikiPage = WikiPage::find()
                        ->where([
                            'ns'          => $page['ns'],
                            'title'       => $page['title'],
                            'language_id' => $this->language->id,
                        ])
                        ->one();

                    //If the page doesn't exist is created
                    if ($wikiPage == null) {
                        $wikiPage = new WikiPage([
                            'ns'          => $page['ns'],
                            'language_id' => $this->language->id,
                            'title'       => $page['title'],
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
                            'user_id'      => $this->user_id,
                            'wiki_page_id' => $wikiPage->id,
                        ])
                        ->one();

                    if ($relation == null) {
                        $relation = new UserWikiPage([
                            'user_id'      => $this->user_id,
                            'wiki_page_id' => $wikiPage->id,
                        ]);

                        $relation->save();
                    }
                }
            } else {
                $error = "Couldn't get list of watchlist";
                throw new ServerErrorHttpException($error);
            }

            if (isset($data['continue']['wrcontinue'])) {
                $this->firstIteration = false;
                $this->_continue = $data['continue']['wrcontinue'];
                $this->run();
            } else {
                //Delete all pages of the current language that aren't in the userPagesId list
                $pagesToDrop = WikiPage::find()
                    ->joinWith('users')
                    ->select('{{%wiki_page}}.id')
                    ->where(['language_id' => $this->language->id])
                    ->andWhere(['user_id' => $this->user_id])
                    ->andWhere(['not in', 'wiki_page_id', $this->userPagesId])
                    ->column();

                if (count($pagesToDrop) > 0) {
                    UserWikiPage::deleteAll([
                        'and',
                        ['user_id' => $this->user_id],
                        ['IN', 'wiki_page_id', $pagesToDrop],
                    ]);
                }
            }

            return true;
        }

        $error = 'No response';
        throw new ServerErrorHttpException($error);
    }
}
