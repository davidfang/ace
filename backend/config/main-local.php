<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5cri1v5pxN2TdTRRs6mP5Zv3TvFyS4QC',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    //$config['modules']['gii'] = 'yii\gii\Module';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators'=>[
            'controller' => [
                'class' => 'yii\gii\generators\controller\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/controller/default',
                ]
            ],
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/crud/default',
                ]
            ],
            'module' => [
                'class' => 'yii\gii\generators\module\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/module/default',
                ]
            ],
            'form' => [
                'class' => 'yii\gii\generators\form\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/form/default',
                ]
            ],
            'model' => [
                'class' => 'yii\gii\generators\model\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/model/default',
                    'common' => '@app/giiTemplates/model/common',
                    'app' => '@app/giiTemplates/model/app',
                ]
            ],
            'extension' => [
                'class' => 'yii\gii\generators\extension\Generator',
                'templates' => [
                    'my' => '@app/giiTemplates/extension/default',
                ]
            ],

        ]

    ];
}

return $config;
