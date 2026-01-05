<?php
namespace frontend\controllers;

use SebastianBergmann\CodeCoverage\Report\Xml\Report;
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

        $activeMeters = [];
        $problemMeters = [];
        $inactiveMeters = [];
        $reportsCount = 0;
        $readingsCount = 0;

        if($user->isTechnician()){
            $activeMeters = Meter::find()->andWhere(['enterpriseID' => $user->technicianinfos->enterpriseID, 'state' => 1])->count();
            $problemMeters = Meter::find()->andWhere(['enterpriseID' => $user->technicianinfos->enterpriseID, 'state' => 2])->count();
            $inactiveMeters = Meter::find()->andWhere(['enterpriseID' => $user->technicianinfos->enterpriseID, 'state' => 0])->count();

            $meterIDs = Meter::find()->andWhere(['enterpriseID' => $user->technicianinfos->enterpriseID])->select('id')->column();
            $reportsCount = Meterproblem::find()->where(['meterID' => $meterIDs])->count();
            $readingsCount = Meterreading::find()->where(['meterID' => $meterIDs])->count();
        }else{
            $activeMeters = Meter::find()->andWhere(['userID' => $user->id, 'state' => 1])->count();
            $problemMeters = Meter::find()->andWhere(['userID' => $user->id, 'state' => 2])->count();
            $inactiveMeters = Meter::find()->andWhere(['userID' => $user->id, 'state' => 0])->count();
            $reportsCount = Meterproblem::find()->andWhere(['userID' => $user->id])->count();

            $meterIDs = Meter::find()->andWhere(['userID' => $user->id])->select('id')->column();
            $readingsCount = Meterreading::find()->andWhere(['meterID' => $meterIDs])->count();
        }

        return $this->render('index', [
            'activeMeters' => $activeMeters,
            'problemMeters' => $problemMeters,
            'inactiveMeters' => $inactiveMeters,

            'isTechnician' => $user->isTechnician(),
            'reportsCount' => $reportsCount,
            'readingsCount' => $readingsCount,
        ]);
    }
}
