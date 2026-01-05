<?php

namespace frontend\controllers;


class SettingsController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = \common\models\User::findOne(\Yii::$app->user->id);
        return $this->render('index');
    }

}
