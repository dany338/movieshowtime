<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\modules\v1\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'basePath' => '@api/modules/v1',
            'class' => 'api\modules\v1\Module'
        ]
    ],
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'acceptParams' => ['version' => 'v1'],
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('access-token')) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'status'  => $response->statusCode,
                        'data'    => $response->data,
                    ];
                    $response->statusCode = \Yii::$app->response->statusCode;
                }
            },
        ],
        'request' => [
            'class' => '\yii\web\Request',
            'csrfParam' => '_csrf-api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'cookieValidationKey' => 'lUH8b8mQy0PASO51wpmUQ6SDgsYa3toS',
        ],
        'user' => [
            'identityCookie' => [
                'name'     => '_backendIdentity',
                'path'     => '/',
                'httpOnly' => true,
            ],
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
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                  'class' => 'yii\rest\UrlRule',
                  'controller' => [
                    'v1/users',
                  ],
                  'tokens' => [
                      '{id}' => '<id:\\w+>'
                  ],
                  'extraPatterns' => [
                      'POST login' => 'login', // 'xxxxx' refers to 'actionXxxxx'
                      'OPTIONS options' => 'options', // 'xxxxx' refers to 'actionXxxxx'
                      'POST create' => 'create',
                      'POST forgot-password' => 'forgot-password',
                      'GET obtener' => 'obtener',
                  ],
                ],
                [
                  'class' => 'yii\rest\UrlRule',
                  'controller' => [
                    'v1/service',
                  ],
                  'tokens' => [
                      '{id}' => '<id:\\w+>'
                  ],
                  'extraPatterns' => [
                      'OPTIONS options' => 'options', // 'xxxxx' refers to 'actionXxxxx'
                      'GET search'      => 'search',
                      'POST create'     => 'create',
                      'POST solicitar'  => 'solicitar',
                      'PUT update'      => 'update',
                  ],
                ],
            ],
          ],
          'Yii2Twilio' => [
            'class' => 'filipajdacic\yiitwilio\YiiTwilio',
            'account_sid' => 'ACd367add69e0c41ba5b1513059db0108d',
            'auth_key' => 'd99ef462f3455fb1cafa57f50cc03c06',
          ],
          'onesignal' => [
            'class' => 'rocketfirm\onesignal\OneSignal',
            'appId' => '073dab20-d9fb-4fb5-b226-6e1ee09acfdc',
            'apiKey' => 'MTNkMDUyOGMtZjU0NC00MTI1LWE0ZTctYTMzODMxZTQwYmZh'
          ]
    ],
    'params' => $params,
];
