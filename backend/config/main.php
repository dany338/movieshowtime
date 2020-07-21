<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'OSlUK9a7qXkQBfiQlKO4nLQp9P6Qkby4',
        ],
        'user' => [
            'identityClass' => 'dektrium\user\models\User',
            'identityCookie' => [
              'name'     => '_identity-backend',
              'path'     => '/',
              'httpOnly' => true,
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
            'cookieParams' => [
              'httpOnly' => true,
              'path'     => '/',
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
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
          'class' => 'yii\web\UrlManager',
          'enablePrettyUrl' => true,
          'showScriptName' => false,
          'rules' => [
          ],
        ],
    ],
    'params' => $params,
];
