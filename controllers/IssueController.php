<?php

namespace app\controllers;

use app\models\Issue;
use app\models\IssueSearch;
use app\models\User;
use app\models\UserIssueVote;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Setting;

/**
 * IssueController implements the CRUD actions for Issue model.
 */
class IssueController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Issue models.
     * @return mixed
     */
    public function actionIndex($viewYours = false)
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new IssueSearch();
        $dataProvider = $searchModel->search($params);

        if ($viewYours) {
            $params['viewYours'] = true;
        }

        $countYours = Issue::find()->where(['user_id' => Yii::$app->user->identity->id])->count();
        $countNew = Issue::getNewIssuesCount();

        $maxIssueSetting = Setting::findOne(['key' => 'issue_quantity_value_per_one_rating']);
        $maxIssueValue = $maxIssueSetting->value;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'countYours' => $countYours,
            'countNew' => $countNew,
            'params' => $params,
            'viewYours' => $viewYours,
            'maxIssueValue' => $maxIssueValue,
        ]);
    }

    /**
     * Displays a single Issue model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $userId = Yii::$app->user->id;
        $user = User::findOne($userId);
        $weightage = $user->getOverallRatingPercent();

        $model = $this->findModel($id);
        $votes = $model->getUserVotesPercent(false);
        return $this->render('view', [
            'model' => $model,
            'weightage' => $weightage,
            'votes' => $votes,
        ]);
    }

    /**
     * Creates a new Issue model.
     * @return mixed
     */
    public function actionCreate()
    {
        $issue = new Issue(['user_id' => Yii::$app->user->identity->id, 'created_at' => time()]);

        if ($this->saveIssue($issue)) {

            //Set creator own vote to Yes
            $issueVote = new UserIssueVote(['issue_id' => $issue->id, 'user_id' => Yii::$app->user->identity->id, 'created_at' => time(), 'vote_type' => UserIssueVote::YES]);

            if (!$issueVote->hasErrors()) {
                $issueVote->save();
            }
            $this->redirect(['issue/view', 'id' => $issue->id]);
        }

        return $this->render('create', [
            'issue' => $issue,
        ]);
    }

    /**
     * Updates an existing Issue model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEdit($id)
    {
        $issue = $this->findModel($id);

        if ($this->saveIssue($issue)) {
            $this->redirect(['issue/view', 'id' => $issue->id]);
        }

        return $this->render('update', [
            'issue' => $issue,
        ]);
    }

    /**
     * Process saving data for new as well as update issue.
     * @param Issue $issue
     * @return bool
     */
    protected function saveIssue($issue)
    {
        if ($issue->load(Yii::$app->request->post())) {
            if (!$issue->hasErrors() && $issue->save()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deletes an existing Issue model.
     * @param integer $id
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        //The user can edit / delete his issue only if there are no other users' votes for the appeal. the vote of the creator does not affect this condition.
        $uservotes = UserIssueVote::getIssueVoteCount($id, true);
        if ($uservotes > 0) {
            $this->redirect(['/issue']);
        }

        $issue = $this->findModel($id);
        $issue->delete();
        $this->redirect(['/issue']);
    }

    /**
     * Vote for an issue.
     * @return integer
     */
    public function actionVote()
    {
        if (Yii::$app->request->isAjax) {
            $postdata = Yii::$app->request->post();

            $issueVote = UserIssueVote::find()->where(['issue_id' => $postdata['issue_id'], 'user_id' => Yii::$app->user->identity->id])->one();
            if (empty($issueVote)) {
                $issueVote = new UserIssueVote(['issue_id' => $postdata['issue_id'], 'user_id' => Yii::$app->user->identity->id, 'created_at' => time()]);
            }

            $issueVote->vote_type = $postdata['type'];

            if (!$issueVote->hasErrors()) {
                if ($issueVote->save()) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * Finds the Issue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Issue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Issue::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
