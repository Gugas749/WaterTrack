<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\helpers\ArrayHelper;
use common\models\Meterreading;
use common\models\Meter;
use common\models\User;
use common\models\Technicianinfo;

class ReadingController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
        $user = Yii::$app->user->identity;
        $isTechnician = $user->isTechnician();

        $selectedMeterId = Yii::$app->request->get('meter_id');
        $readingId = Yii::$app->request->get('id');

        if ($isTechnician) {
            $enterpriseIds = Technicianinfo::find()
                ->select('enterpriseID')
                ->where(['userID' => $user->id])
                ->column();

            $meterIds = Meter::find()
                ->select('id')
                ->where(['enterpriseID' => $enterpriseIds])
                ->column();

            $readings = Meterreading::find()
                ->where(['meterID' => $meterIds])
                ->all();
        } else {
            $readings = Meterreading::find()
                ->joinWith('meter')
                ->where(['meter.userID' => $user->id])
                ->all();
        }

        $detailReading = $readingId ? Meterreading::findOne($readingId) : null;

        if ($detailReading) {
            if (
                (!$isTechnician && $detailReading->meter->userID !== $user->id) ||
                ($isTechnician && !in_array($detailReading->meterID, $meterIds ?? []))
            ) {
                $detailReading = null;
            }
        }

        $meters = $isTechnician
            ? Meter::find()->where(['id' => $meterIds])->all()
            : Meter::find()->where(['userID' => $user->id])->all();

        $meterOptions = ArrayHelper::map(
            $meters,
            'id',
            fn($m) => 'Contador #' . $m->id . ' - ' . $m->address
        );

        $technician = $detailReading
            ? User::findOne($detailReading->tecnicoID)
            : null;

        $meterItems = ArrayHelper::map($meters, 'id', 'address');

        if($selectedMeterId != null && $selectedMeterId > 0){
            $aux = [];
            foreach ($readings as $reading) {
                if($reading->meterID == $selectedMeterId){
                    $aux[] = $reading;
                }
            }
            $readings = $aux;
        }

        return $this->render('index', [
            'readings' => $readings,
            'detailReading' => $detailReading,
            'isTechnician' => $isTechnician,
            'meterOptions' => $meterOptions,
            'technician' => $technician,
            'selectedMeterId' => $selectedMeterId,
            'meterItems' => $meterItems,
        ]);
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        if (!$user->isTechnician()) {
            throw new ForbiddenHttpException();
        }

        $model = new Meterreading();
        $model->date = date('Y-m-d');
        $model->tecnicoID = $user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        }

        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        if (!Yii::$app->user->identity->isTechnician()) {
            throw new ForbiddenHttpException();
        }

        $model = Meterreading::findOne($id);

        if ($model && $model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->redirect(['index', 'id' => $id]);
    }
}
