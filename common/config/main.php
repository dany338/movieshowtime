<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'en',
    'sourceLanguage' => 'en',
    'name' => 'Movie Show Time Finder',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
          'class' => 'components\MyManager',
        ],
        'urlManager' => [
          'class' => 'yii\web\UrlManager',
          'enablePrettyUrl' => true,
          'showScriptName' => false,
        ],
    ],
    'modules' => [
      'user' => [
          'class' => 'dektrium\user\Module',
        ],
      'rbac' => [
        'class' => 'dektrium\rbac\RbacWebModule'
      ],
      'dynagrid'=> [
        'class'=>'\kartik\dynagrid\Module',
        // other module settings
      ],
      'gridview'=> [
        'class'=>'\kartik\grid\Module',
        // other module settings
        /*'i18n' => [
          'class' => 'yii\i18n\PhpMessageSource',
          'basePath' => '@kvgrid/messages',
          'forceTranslation' => true
        ]*/
      ],
      'markdown' => [
        // the module class
        'class' => 'kartik\markdown\Module',
        // the controller action route used for markdown editor preview
        'previewAction' => '/markdown/parse/preview',

        // the list of custom conversion patterns for post processing
        'customConversion' => [
          '<table>' => '<table class="table table-bordered table-striped white">'
        ],

        // whether to use PHP SmartyPantsTypographer to process Markdown output
        'smartyPants' => true,
      ]
    ],
];
