<?php

namespace backend\controllers;

use backend\models\Addreadingform;
use common\models\Enterprise;
use common\models\Meter;
use common\models\Meterproblem;
use common\models\Meterreading;
use common\models\User;
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
        $readingIdParam = Yii::$app->request->get('id');
        $enterpriseID = Yii::$app->request->get('enterprise_id');
        $meterID = Yii::$app->request->get('meter_id');
        $selectedMeterIDForTechnicians = Yii::$app->request->get('meterID');

        $enterpriseID = $enterpriseID !== '' ? $enterpriseID : null;
        $meterID = $meterID !== '' ? $meterID : null;

        $detailReading = null;
        $technician = null;
        $meterItems = null;
        $accumulatedConsumptionTotal = 0;
        $waterPressureTotal = 0;
        $accumulatedConsumption = 0;
        $waterPressure = 0;

        $enterprises = Enterprise::find()->all();
        $meters = Meter::find()->all();
        $readings = Meterreading::find()->all();
        $problems = Meterproblem::find()->all();

        // Filter detail reading if an ID is provided
        if ($readingIdParam !== null) {
            $detailReading = Meterreading::findOne($readingIdParam);
            if ($detailReading) {
                $technician = User::findOne($detailReading->tecnicoID);
            }
        }

        // Filter readings and meters by enterprise
        if ($enterpriseID !== null) {
            $readings = [];
            $meters = Meter::find()->where(['enterpriseID' => $enterpriseID])->all();
            foreach ($meters as $meter) {
                $readings = array_merge(
                    $readings,
                    Meterreading::find()->where(['meterID' => $meter->id])->all()
                );
            }
            $meterItems = ArrayHelper::map($meters, 'id', 'address');
        }

        // Filter readings by specific meter
        if ($enterpriseID !== null && $meterID !== null) {
            $readings = Meterreading::find()->where(['meterID' => $meterID])->all();
        }

        // Calculate averages
        foreach ($readings as $reading) {
            $accumulatedConsumptionTotal += $reading->accumulatedConsumption;
            $waterPressureTotal += $reading->waterPressure;
        }
        if(count($readings) > 0){
            $accumulatedConsumption = $accumulatedConsumptionTotal / count($readings);
            $waterPressure = $waterPressureTotal / count($readings);
        }

        // PJAX: Filter technicians based on selected meter
        $users = [];
        if ($selectedMeterIDForTechnicians) {
            $meter = Meter::findOne($selectedMeterIDForTechnicians);
            if ($meter) {
                $aux = User::find()->innerJoinWith('technicianinfos')->all();

                foreach ($aux as $user) {
                    if($user->technicianinfos->enterpriseID == $meter->enterpriseID) {
                        $users = array_merge($users, [$user]);
                    }
                }
            }
        } else {
            $users = User::find()
                ->innerJoinWith('technicianinfos')
                ->all();
        }

        return $this->render('index', [
            'users' => $users,
            'readings' => $readings,
            'meterItems' => $meterItems,
            'selectedEnterpriseId' => $enterpriseID,
            'selectedMeterId' => $meterID,
            'enterpriseItems' => ArrayHelper::map($enterprises, 'id', 'name'),
            'detailReading' => $detailReading,
            'technician' => $technician,
            'addReadingModel' => new Meterreading(),
            'meters' => $meters,
            'problems' => $problems,
            'accumulatedConsumption' => number_format($accumulatedConsumption, 2),
            'waterPressure' => number_format($waterPressure, 2),
            'selectedMeterIDForTechnicians' => $selectedMeterIDForTechnicians, // send to view for PJAX
        ]);
    }

    public function actionCreate()
    {
        $form = new Addreadingform();

        $post = Yii::$app->request->post();

        if ($form->load(['Addreadingform' => $post['Meterreading']]) && $form->createReading()) {
            Yii::$app->session->setFlash('success', 'Leitura criada com sucesso!');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Erro ao criar leitura.');
        return $this->redirect(['index']);
    }
}