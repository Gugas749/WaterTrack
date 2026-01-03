<?php

namespace backend\controllers;

use backend\models\Addmetertypeform;
use common\models\Metertype;
use common\models\Meter;
use Yii;
use yii\web\Controller;

class ExtrasController extends Controller
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
        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');

        $query = \common\models\Metertype::find();
        $detailMeterTypes = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'description', $search]);
        }
        $meterTypes = $query->all();

        if($detail){
            $detailMeterTypes = Meter::findOne($detail);
        }

        return $this->render('index', [
            'meterTypes' => $meterTypes,
            'search' => $search,
            'addMeterTypeModel' => new Addmetertypeform(),
            'detailMeterTypes' => $detailMeterTypes,
        ]);
    }

    public function actionCreate()
    {
        $model = new Addmetertypeform();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->createMeterType()) {
                Yii::$app->session->setFlash('success', 'Tipo de contador criado com sucesso!');
                return $this->redirect(['index']);
            } else {
                Yii::error('Create failed: ' . json_encode($model->getErrors()), __METHOD__);
            }
        }
        $meterTypes = Metertype::find()->all();

        return $this->render('index', [
            'addMeterTypeModel' => $model,
            'meterTypes' => $meterTypes,
            'detailMeterTypes' => null,
        ]);
    }


    public function actionUpdate($id)
    {
        $model = Metertype::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Tipo de contador não encontrado.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tipo de Contador atualizado com sucesso!');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Metertype::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Tipo de contador não encontrado.');
            return $this->redirect(['index']);
        }
        $associatedMeters = Meter::find()->where(['meterTypeID' => $id])->count();
        if ($associatedMeters > 0) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Existem associações ativas!');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Tipo de Contador eliminado com sucesso!');
        return $this->redirect(['index']);
    }


}