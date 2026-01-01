<?php

namespace backend\controllers;

use backend\models\Addmeterform;
use common\models\Enterprise;
use common\models\Meter;
use common\models\Metertype;
use common\models\User;
use yii\helpers\Json;
use Yii;
use yii\web\Controller;

class MeterController extends Controller
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
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        // $changeStatus = Yii::$app->request->get('s');
        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');

        $query = \common\models\Meter::find();

        $detailMeter = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'address', $search]);
        }
        $meters = $query->all();

        if($detail){
            $detailMeter = Meter::findOne($detail);
        }

        return $this->render('index', [
            'meters' => $meters,
            'search' => $search,
            'addMeterModel' => new AddMeterForm(),
            'meterTypes' => MeterType::find()->all(),
            'enterprises' => Enterprise::find()->all(),
            'users' => User::find()->all(),
            'detailMeter' => $detailMeter,
        ]);
    }

    public function actionCreate()
    {
        $model = new Addmeterform();

        if ($model->load(Yii::$app->request->post()) && $model->createmeter()) {
            Yii::$app->session->setFlash('success', 'Contador criado com sucesso!');
            return $this->redirect(['index']);
        } else {
            Yii::error('CreateMeter failed: ' . json_encode($model->getErrors()), __METHOD__);
            Yii::$app->session->setFlash('error', 'Ação Falhada: Contactar Administrador [M-1]');
        }

        $meters = Meter::find()->all();
        return $this->render('index', [
            'addMeterModel' => $model,
            'meters' => $meters,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Meter::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Contador não encontrado.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Contador atualizada com sucesso!');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUpdateState($id)
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if (isset($id, $post['state'])) {
                $meter = Meter::findOne($id);
                if ($meter) {
                    $meter->state = (int)$post['state'];
                    $meter->save(false);
                    Yii::$app->session->setFlash('success', 'Contador atualizado com sucesso!');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('index', [
            'meter' => $meter
        ]);
    }
}