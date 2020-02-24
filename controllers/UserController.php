<?php

namespace app\controllers;

use Yii;
use yii\db\Expression;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\authclient\OAuth2;
use app\models\ContactForm;
use app\models\FileEntry;
use app\models\FileEntrySearch;
use app\models\User;
use yii\data\Pagination;

use yii\grid\GridView;
use yii\data\ActiveDataProvider;

class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if(Yii::$app->user->isGuest && Yii::$app->controller->action->id !== 'login')
		{
			Yii::$app->session->setFlash('error', 'You must login to access this feature.');
            
			$this->goHome();
		}
        
        return parent::beforeAction($action);
    }
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

		Yii::$app->session->setFlash('success', 'You have successfully logged out.');

        return $this->goHome();
    }
    
	public function actionAccount()
	{
        if(!Yii::$app->user->isGuest)
        {
            return $this->render('account');
        }
	}
	
    public function actionLogin()
    {
        $provider = new \TheNetworg\OAuth2\Client\Provider\Azure([
            'clientId'          => 'ef813a42-eb3a-4395-9e6c-94128b1c1022',
            'clientSecret'      => 'k7T[__PsdOGSmtZ/J38MvMkqWFsiKIW9',
            'redirectUri'       => 'https://share.adcitservices.com/user/login'
        ]);
        
        if (!isset($_GET['code'])) {
        
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;
        
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        
        } else {
        
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'],
                'resource' => 'https://graph.windows.net',
            ]);
            
            $me = $provider->get("me", $token);
            
            $resourceOwner = $provider->getResourceOwner($token);
            
            if(isset($me) and isset($resourceOwner))
            {
                $existingUser = User::findOne(['ms_id' => $resourceOwner->getId()]);
                
                if(isset($existingUser))
                {
                    $existingUser->username = $resourceOwner->getUpn();
                    $existingUser->ms_email = $me['mail'];
                    $existingUser->ms_id = $resourceOwner->getId();
                    $existingUser->ms_firstname = $resourceOwner->getFirstName();
                    $existingUser->ms_lastname = $resourceOwner->getLastName();
                    $existingUser->ms_tenantid = $resourceOwner->getTenantId();
                    $existingUser->save();
                    
                    Yii::$app->session->setFlash('success', 'You have successfully logged in.');
                    Yii::$app->user->login($existingUser, 7 * 24 * 60 * 60);
                    
                    return $this->goHome();
                }
                else
                {
                    $newUser = new User();
                    $newUser->username = $resourceOwner->getUpn();
                    $newUser->ms_email = $me['mail'];
                    $newUser->ms_id = $resourceOwner->getId();
                    $newUser->ms_firstname = $resourceOwner->getFirstName();
                    $newUser->ms_lastname = $resourceOwner->getLastName();
                    $newUser->ms_tenantid = $resourceOwner->getTenantId();
                    $newUser->auth_key = Yii::$app->getSecurity()->generateRandomString();
                    $newUser->save();

                    Yii::$app->session->setFlash('success', 'You have successfully logged in.');
                    Yii::$app->user->login($newUser, 7 * 24 * 60 * 60);

                    return $this->goHome();
                }
            }
            else
            {
                Yii::$app->session->setFlash('error', 'Something went wrong, please try to log in again.');
                return $this->goHome();
            }


            /*
            // Optional: Now you have a token you can look up a users profile data
            try {
        
                // We got an access token, let's now get the user's details
                $me = $provider->get("me", $token);
        
                // Use these details to create a new profile
                echo $me['givenName'];
        
            } catch (Exception $e) {
        
                // Failed to get user details
                exit('Oh dear...');
            }
            */
            
            // Use this to interact with an API on the users behalf
            //echo $token->getToken();
        }
    }
	
    public function actionLibrary()
    {
        if(!Yii::$app->user->isGuest)
        { 
            $query = FileEntry::find()
                ->where(['user' => Yii::$app->user->identity->ms_id])
                ->orWhere(['in', 'auth_id_list', Yii::$app->user->identity->id]);
                
            $searchModel = new FileEntrySearch();
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);
               
            if(Yii::$app->request->isAjax)
            {
                $dataProvider = $searchModel->search(Yii::$app->request->get());
            }
            
            return $this->render('library', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        }
    }
    
    public function actionResetAuthKey()
    {
        $request = Yii::$app->request;

        if ($request->isAjax)
        {
            if(isset($_POST['reset']) && $_POST['reset'] == true)
            {
                $uid = Yii::$app->user->identity->id;
                
                $model = User::find()
                    ->where(['id' => $uid])
                    ->one();
                    
                $last = Yii::$app->user->identity->last_auth_reset;
                $current = time();
                
                $waitedtime = $current-$last;
                
                if($model->last_auth_reset == null || $waitedtime >= 1800)
                {                    
                    $model->auth_key = Yii::$app->getSecurity()->generateRandomString();
                    $model->last_auth_reset = time();
                    $model->save();
                    
                    Yii::$app->session->setFlash('success', 'Your authorization key has been reset.');
                    //$this->redirect(['user/account']);
                }
                else
                {
                    $timeleft = Yii::$app->formatter->asDuration(1800-$waitedtime);
                    
                    Yii::$app->session->setFlash('error', 'You need to wait <b>' . $timeleft . '</b> before you can reset your authorization key.');
                }
            }
        }
    }
}
