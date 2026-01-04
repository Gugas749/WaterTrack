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

        $enterpriseID = $enterpriseID !== '' ? $enterpriseID : null;
        $meterID = $meterID !== '' ? $meterID : null;

        $detailReading = null;
        $technician = null;
        $selectedDetailsProblem = null;
        $meterItems = null;

        $enterprises = Enterprise::find()->all();
        $meters = Meter::find()->all();
        $readings = Meterreading::find()->all();
        $problems = Meterproblem::find()->all();

        if ($readingIdParam !== null) {
            $detailReading = Meterreading::findOne($readingIdParam);
            if ($detailReading) {
                $technician = User::findOne($detailReading->userID);
                $selectedDetailsProblem = Meterproblem::findOne($detailReading->problemID);
            }
        }

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

        if ($enterpriseID !== null && $meterID !== null) {
            $readings = Meterreading::find()->where(['like', 'meterID', $meterID])->all();
        }

        return $this->render('index', [
            'users' => User::find()->all(),
            'readings' => $readings,
            'meterItems' => $meterItems,
            'selectedEnterpriseId' => $enterpriseID,
            'selectedMeterId' => $meterID,
            'enterpriseItems' => ArrayHelper::map($enterprises, 'id', 'name'),
            'detailReading' => $detailReading,
            'technician' => $technician,
            'selectedDetailsProblem' => $selectedDetailsProblem,
            'addReadingModel' => new Meterreading(),
            'meters' => $meters,
            'problems' => $problems,
        ]);
    }

    public function actionCreate()
    {
        $model = new Addreadingform();

        Yii::error(Yii::$app->request->post(), __METHOD__);

        if ($model->load(Yii::$app->request->post()) && $model->createReading()) {
            Yii::$app->session->setFlash('success', 'Leitura criada com sucesso!');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Erro ao criar leitura.');
        return $this->redirect(['index']);
    }
}