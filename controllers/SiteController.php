<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\authclient\OAuth2;
use app\models\ContactForm;
use app\models\FileEntry;
use app\models\User;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionGlobal()
    {
        if(!Yii::$app->user->isGuest)
        {
            $records = FileEntry::find()->all();
            
            $count = FileEntry::find()->count();
            
            $view = 0;
            
            $dl = 0;
            
            foreach ($records as $record)
            {
                $view = $view + $record->views;
                
                $dl = $dl + $record->downloads;
            }
            
            echo "Global files: " . $count . "<br>";
            echo "Global views: " . $view . "<br>";
            echo "Global downloads: " . $dl;        
        }
    }
}
