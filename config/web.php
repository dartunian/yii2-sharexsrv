<?php
use kartik\datecontrol\Module;
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'ADC Share Tool',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'timeZone' => 'America/Los_Angeles',
    'components' => [
        'thumbnail' => [
            'class' => 'sadovojav\image\Thumbnail',
            'cachePath' => '@webroot/thumbnails',
            'options' => [
                'placeholder' => [
                    'type' => sadovojav\image\Thumbnail::PLACEHOLDER_TYPE_IMAGINE,
                    'backgroundColor' => '3a3d3f',
                    'textColor' => 'fff',
                    'textSize' => 30,
                    'text' => 'Oops!'
                ],
                'quality' => 92,
            ]
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index.php
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
            'rules' => array(
                    //'<controller:\w+>/<id:\d+>' => '<controller>/view',
                    //'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                    //'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                    //'<controller>/<action>/<id>' => '<controller>/<action>',
                    'raw/<id>' => 'file/view-raw',
                    'view/<id>' => 'file/view-file',
                    'dl/<id>' => 'file/view-download',
                    'load/<id>' => 'file/load-image',                    
                    'upload' => 'file/upload',                                        
                    'library' => 'user/library',
                    'account' => 'user/account',
                    'resetkey' => 'user/reset-auth-key', 
            ),
        ],
        'assetManager' => [
            'bundles' => [
                'yii\widgets\PjaxAsset' => [
                    'sourcePath' => '@vendor/swilson1337/jquery-pjax-queued',
                    'js' => [
                        'jquery.pjax.js',
                    ],
                    'jsOptions' => [
                        'position' => \yii\web\View::POS_HEAD,
                    ],
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'gsNG0aVUUfENzuVyikHqpB_lEHCYhqsC',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'authTimeout' => 1 * 24 * 60 * 60,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'modules' => [
        'noty' => [
            'class' => 'lo\modules\noty\Module',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',  
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['76.103.14.185'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
