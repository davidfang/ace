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
    'name' => '集团OA',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                ],
            ],
        ],
        'request'=>[
            'enableCsrfValidation'=>false,
        ],
        'user' => [
            'identityClass' => 'app\models\AdminUser',
            'enableAutoLogin' => true,
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
            'class'=>'yii\rbac\DbManager'
        ]
    ],
    'params' => $params,
];
