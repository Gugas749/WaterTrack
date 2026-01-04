<?php

namespace backend\controllers;

use common\models\Loginform;
use common\models\Meter;
use common\models\Meterreading;
use common\models\User;
use Yii;
use yii\db\Expression;
use yii\httpclient\Client;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'except' => ['error'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return Yii::$app->response->redirect(['site/login']);                },
            ],
        ];
    }

    public function actionIndex()
    {
        $currentYear = Yii::$app->request->get('year', date('Y')); // default é o atual

        //-----------Bar Chart------------
        $monthlyReadings = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyReadings[$month] = Meterreading::find()
                ->where(new Expression('MONTH(`date`) = :month AND YEAR(`date`) = :year', [
                    ':month' => $month,
                    ':year' => $currentYear
                ]))
                ->all();
        }

        $lastReadings = Meterreading::find()->orderBy(['id' => SORT_DESC])->limit(10)->all();

        return $this->render('index', [
            'currentYear' => $currentYear,
            'lastReadings' => $lastReadings,

            // Bar chart
            'readingsJan' => $monthlyReadings[1],
            'readingsFev' => $monthlyReadings[2],
            'readingsMar' => $monthlyReadings[3],
            'readingsAbr' => $monthlyReadings[4],
            'readingsMai' => $monthlyReadings[5],
            'readingsJun' => $monthlyReadings[6],
            'readingsJul' => $monthlyReadings[7],
            'readingsAgo' => $monthlyReadings[8],
            'readingsSet' => $monthlyReadings[9],
            'readingsOut' => $monthlyReadings[10],
            'readingsNov' => $monthlyReadings[11],
            'readingsDez' => $monthlyReadings[12],

            // Donut chart
            'metersActive' => Meter::find()->where(['state' => 1])->all(),
            'metersProblem' => Meter::find()->where(['state' => 2])->all(),
            'metersInactive' => Meter::find()->where(['state' => 0])->all(),

            // Cards
            'activeMeterCount' => $this->getActiveMeterCount(),
            'readingCount' => $this->getReadingCount(),
            'userCount' => $this->getUserCount(),
        ]);
    }

    public function getActiveMeterCount()
    {
        $meters=Meter::find()->all();
        $meterCount = 0;
        foreach ($meters as $meter) {
            if($meter->state==1 || $meter->state==2){
                $meterCount++;
            }
        }
        return $meterCount;
    }

    public function getReadingCount()
    {
        $readings=Meterreading::find()->all();
        return count($readings);
    }

    public function getUserCount()
    {
        $users=User::find()->all();
        return count($users);
    }
}