<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'defaultRoute'=>'home',
    'language'=>'zh-CN',
    'controllerNamespace' => 'backend\controllers',
    'name' => '管理系统',
    'bootstrap' => ['log'],
    'layout' => 'main',
    'modules' => [
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
            //Some controller property maybe need to change.
            'controllerMap' => [
                'assignment' => [
                    'class' => 'yii2mod\rbac\controllers\AssignmentController',
                    'userClassName' => 'app\models\AdminUser',
                ]
            ]
        ],
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@vendor/zc/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                ],
            ],
        ],
        'request'=>[
            'enableCsrfValidation'=>false,
        ],
        'user' => [
            'identityClass' => 'app\models\AdminUser',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
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
            'errorAction' => 'home/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules'=>[
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
                ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
            ],
        ],
        'authManager'=>[
            'class'=>'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
            'cache' => 'yii\caching\FileCache',
        ]
    ],
    'params' => $params,
];
