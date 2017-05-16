<?php

$config = [
    'id' => 'admin',
    'name' => 'Admin',
    'language' => 'en',
    'sourceLanguage' => 'en',
    'basePath' => dirname(__DIR__),
    'as beforeRequest' => [
        'class' => \yii\filters\auth\HttpBasicAuth::className(),
        'auth' => function ($username, $password) {
            return \app\models\User::auth($username, $password);
        },
        'except' => ['site/error'],
        'realm' => 'Authorization needed'
    ],
    'on beforeAction' => function ($event) {
        Yii::$app->layout = Yii::$app->user->isGuest ? '@app/views/layouts/guest.php' : '@app/views/layouts/main.php';
    },
    'components' => [
        'request' => [
            'cookieValidationKey' => 'rH0OWgWE6TTJaGk31OIcW2XZwENzhFE5',
            'baseUrl' => app\components\Env::get("BASE_URL")
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
        ],
        'db' => [
            'class' => 'yii\mongodb\Connection',
            'dsn' => app\components\Env::get('MONGO_DB_URL')
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'index' => 'site/index',
                'movie/poster/<image:\w+\.[jpg|png|gif]+>' => 'movie/poster',
                '<controller>' => '<controller>/index'
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'kartik\editable\EditableAsset' => [
                    'sourcePath' => false,
                    'basePath' => '@webroot/static/editable',
                    'baseUrl' => '@web/static/editable',
                    'js' => [
                        'js/editable.js'
                    ]
                ]
            ]
        ]
    ],
    'modules' => [
        'gridview' => [
            'class' => '\kartik\grid\Module'
        ]
    ]
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'mongoDbModel' => [
                'class' => 'yii\mongodb\gii\model\Generator'
            ]
        ],
    ];

    $config['bootstrap'][] = 'log';
    $config['components']['log'] = [
        'traceLevel' => 3,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ]
    ];
}

return $config;
