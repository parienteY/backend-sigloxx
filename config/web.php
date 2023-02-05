<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
          'class' => 'mdm\admin\Module',
          'layout' => 'left-menu',
          'mainLayout' => '@app/views/layouts/main.php',
        ]
      ],
      'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
          'site/*',
          'admin/*',
          'debug/*',
          'gii/*',
          "auth/*",
          "directorio/*",
          "archivo-publico/*",
          "archivo-privado/*",
          "tag/*",
          "user/*",
          "unidad/*",
          "noticia/*",
          "tiene/*",
          "public/*",
          "web/*"
        ]
      ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // or use 'yii\rbac\DbManager'
        ],
        'request' => [
            'baseUrl' => '',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'xi585mePSFP41D4KrK1lLwSUwuIjFLN2',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
              ]
        ],
        'response' => [
            //    'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
              header("Access-Control-Allow-Origin: *");
              header("Access-Control-Allow-Methods: *");
              header("Access-Control-Allow-Headers: *");
              header("Access-Control-Allow-Credentials: true");
              header("Access-Control-Request-Headers: *");
              header("Access-Control-Expose-Headers: *");
              header("Access-Control-Max-Age: 3600");
            },
          ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
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
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
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
        'allowedIPs' => ['127.0.0.1', '::1', "192.168.88.26", '192.168.100.55'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', "192.168.88.26", '192.168.100.55'],
    ];
}

return $config;
