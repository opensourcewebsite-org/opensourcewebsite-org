<?php

namespace app\controllers;

use Yii;
use app\models\Moqup;
use app\models\User;
use app\models\UserMoqupFollow;
use yii\web\Controller;
use yii\filters\AccessControl;

class UserController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['display', 'follow-moqup', 'unfollow-moqup', 'follow-user', 'unfollow-user'],
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

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionDisplay()
    {
        $confirmed_users = User::findAll(['is_email_confirmed' => true]);

        return $this->render('display', [
            'confirmed_users' => count($confirmed_users),
        ]);
    }

    /**
     * Add a moqup to the list followed by the user
     * @return boolean If the relation was saved
     */
    public function actionFollowMoqup($id)
    {
        $exists = UserMoqupFollow::findOne(['moqup_id' => $id, 'user_id' => Yii::$app->user->identity->id]);

        $withoutErrors = false;

        if ($exists == null) {
            $relation = new UserMoqupFollow([
                'moqup_id' => $id,
                'user_id' => Yii::$app->user->identity->id
            ]);

            if ($relation->save()) {
               $withoutErrors = true;
            }
        }

        echo $withoutErrors;
        exit;
    }

    /**
     * Remove a moqup from the list followed by the user
     * @return boolean If the relation was removed
     */
    public function actionUnfollowMoqup($id)
    {
        $model = UserMoqupFollow::findOne(['moqup_id' => $id, 'user_id' => Yii::$app->user->identity->id]);

        $withoutErrors = false;

        if ($model != null && $model->delete()) {
            $withoutErrors = true;
        }

        echo $withoutErrors;
        exit;
    }

    /**
     * Profile section for the account page
     * 
     */

    public function actionProfile()
    {
        $model = Yii::$app->user->identity;
        
        if($model){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Updated successfully.');
                return $this->redirect('/site/account');
            }
            
            return $this->render('profile', [
                'model' => $model,
            ]);
        }
        
    }
    
    
    /**
     * View profile page for the user
     * 
     */
    
    public function actionView( $id ){
        $model = Yii::$app->user->identity;
        
        if($model){
            return $this->render('view', [
                'model' => $model,
            ]);
        }else{
            
            $referrer = ReferrerHelper::getReferrerFromCookie();
            if ($user = User::findOne($id)) {
                if ($referrer === null) {
                    // first time
                    ReferrerHelper::addReferrer($user);
                } elseif ($referrer->value != $id) {
                    // change refferer
                    ReferrerHelper::changeReferrer($user);
                }
            }
            return $this->redirect('/site/error');
        }
    }
}
