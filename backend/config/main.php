<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);
Yii::setAlias('@contentsViews', '@backend/views/layouts/contents');

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'api' => [
            'class' => 'backend\modules\api\ModuleAPI',
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => null//['site/login'] //REDIRECIONA_TODO O TRAFICO SE TIVER INVÁLIDO
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'defaultRoute' => 'dashboard/index',
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/user',
                        'api/user-profile',
                        'api/technician-info',
                        'api/enterprise',
                        'api/meter',
                        'api/meter-problem',
                        'api/meter-reading',
                        'api/meter-type',
                        'api/auth'
                    ],
                    'extraPatterns' => [
                        // USERS
                        'PUT putstatus/{id}' => 'putstatus',
                        'GET getrole/{id}' => 'getrole',
                        // USER-PROFILE
                        'GET profile/{id}' => 'profile',
                        // TECHNICIAN-INFO
                        'GET techinfo/{id}' => 'techinfo',
                        // METER AND METER-PROBLEM AND METER-READING
                        'GET fromuser/{id}' => 'fromuser',
                        // METER
                        'GET fromenterprise/{id}' => 'fromenterprise',
                        'GET type/{id}' => 'type',
                        'GET withstate/{state}' => 'withstate',
                        // METER-PROBLEM AND METER-READING
                        'GET frommeter/{id}' => 'frommeter',
                        // METER-READING
                        'GET problem/{id}' => 'problem',
                        // AUTH
                        'POST login' => 'login',
                        'POST signup' => 'signup',
                        'POST teste' => 'teste',
                    ],
                    'tokens' => [
                        '{id}' => '<id:\\d+>',
                        '{state}' => '<state:\\d+>',
                    ],
                ],
            ],
        ],
        'as beforeRequest' => [
            'class' => 'yii\filters\AccessControl',
            'except' => ['site/login', 'site/error', 'dashboard/error'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                return Yii::$app->response->redirect(['site/login']);
            },
        ],
    ],
    'params' => $params,
];