<?php

namespace backend\controllers;

use backend\models\Adduserform;
use common\models\Enterprise;
use common\models\Meter;
use common\models\Meterproblem;
use common\models\Meterreading;
use common\models\Technicianinfo;
use common\models\User;
use common\models\Userprofile;
use Yii;
use yii\helpers\ArrayHelper;

class ReadingController extends \yii\web\Controller
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
        //$queryParam = Yii::$app->request->get('q');
        $readingIdParam = Yii::$app->request->get('id');
        $enterpriseID = Yii::$app->request->get('enterprise_id');
        $meterID = Yii::$app->request->get('meter_id');

        $enterpriseID = $enterpriseID !== '' ? $enterpriseID : null;
        $meterID = $meterID !== '' ? $meterID : null;

        $detailReading = null;
        $technician = null;
        $selectedDetailsProblem = null;
        $meterItems = null;

        $enterprises = Enterprise::find()->all();
        $readings = Meterreading::find()->all();

        if ($readingIdParam !== null) {
            $detailReading = Meterreading::find()->where(['id' => $readingIdParam])->one();

            if ($detailReading) {
                $technician = User::find()->where(['id' => $detailReading->userID])->one();
                $selectedDetailsProblem = Meterproblem::find()->where(['id' => $detailReading->problemID])->one();
            }
        }

        // DROPDOWN EMPRESAS SELECTION
        if($enterpriseID !== null){
            $readings = [];

            $meters = Meter::find()->where(['enterpriseID' => $enterpriseID])->all();
            foreach ($meters as $meter) {
                $readings = array_merge(
                    $readings,
                    Meterreading::find()->where(['meterID' => $meter->id])->all()
                );
            }

            $meterItems = \yii\helpers\ArrayHelper::map($meters, 'id', 'address');
        }

        if($enterpriseID !== null && $meterID !== null){
            $readings = [];
            $readings = Meterreading::find()->andWhere(['like', 'meterID', $meterID])->all();
        }


        return $this->render('index', [
            'users' => User::find()->all(),
            'readings' => $readings,
            'meterItems' => $meterItems,
            'selectedEnterpriseId' => $enterpriseID,
            'selectedMeterId' => $meterID,
            'enterpriseItems' => \yii\helpers\ArrayHelper::map($enterprises, 'id', 'name'),

            'detailReading' => $detailReading,
            'technician' => $technician,
            'selectedDetailsProblem' => $selectedDetailsProblem,
        ]);
    }
}