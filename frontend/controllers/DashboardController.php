<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Meter;
use common\models\Meterproblem;
use common\models\Meterreading;



class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // só utilizadores logados
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $isTechnician = $user ? $user->isTechnician() : false;

        $activeMeters = [];
        $problemMeters = [];
        $inactiveMeters = [];

        if($isTechnician){
            $activeMeters = Meter::find()->andWhere(['enterpriseID' => $userId, 'state' => 1])->count();
            $problemMeters = Meter::find()->andWhere(['enterpriseID' => $userId, 'state' => 2])->count();
            $inactiveMeters = Meter::find()->andWhere(['enterpriseID' => $userId, 'state' => 0])->count();
        }else{
            $activeMeters = Meter::find()->andWhere(['userID' => $userId, 'state' => 1])->count();
            $problemMeters = Meter::find()->andWhere(['userID' => $userId, 'state' => 2])->count();
            $inactiveMeters = Meter::find()->andWhere(['userID' => $userId, 'state' => 0])->count();
        }



        // ===== LEITURAS (Meterreading.problemState) =====
        $readingQuery = Meterreading::find()
            ->joinWith('meter');

        if (!$isTechnician) {
            $readingQuery->andWhere(['meter.userID' => $userId]);
        }
        return $this->render('index', [
            'activeMeters' => $activeMeters,
            'problemMeters' => $problemMeters,
            'inactiveMeters' => $inactiveMeters,

            'isTechnician' => $isTechnician,
        ]);
    }
}
