<?php

namespace backend\controllers;

use backend\models\Addenterpriseform;
use common\models\Enterprise;
use common\models\Technicianinfo;
use common\models\Meter;
use Yii;
use yii\web\Controller;

class EnterpriseController extends Controller
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

        $query = \common\models\Enterprise::find();
        $detailEnterprise = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'name', $search]);
        }
        $enterprises = $query->all();

        if($detail){
            $detailEnterprise = Enterprise::findOne($detail);
        }

        return $this->render('index', [
            'enterprises' => $enterprises,
            'search' => $search,
            'addEnterpriseModel' => new Addenterpriseform(),
            'detailEnterprise' => $detailEnterprise,
        ]);
    }

    public function actionCreate()
    {
        $model = new Addenterpriseform();

        if ($model->load(Yii::$app->request->post()) && $model->createEnterprise()) {
            Yii::$app->session->setFlash('success', 'Empresa criada com sucesso!');
            return $this->redirect(['index']);
        } else {
            Yii::error('CreateEnterprise failed: ' . json_encode($model->getErrors()), __METHOD__);
            Yii::$app->session->setFlash('error', 'Ação Falhada: Contactar Administrador [E-1]');
        }

        $enterprises = Enterprise::find()->all();
        return $this->render('index', [
            'addEnterpriseModel' => $model,
            'enterprises' => $enterprises,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Enterprise::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Empresa não encontrado.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Empresa atualizada com sucesso!');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Enterprise::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Empresa não encontrado.');
            return $this->redirect(['index']);
        }
        $associatedMeters = Meter::find()->where(['meterTypeID' => $id])->count();
        $associatedTecnicalInfo = Technicianinfo::find()->where(['enterpriseID' => $id])->count();
        if ($associatedMeters > 0 || $associatedTecnicalInfo > 0) {
            Yii::$app->session->setFlash('error', 'Ação Negada: Existem associações ativas!');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Empresa eliminada com sucesso!');
        return $this->redirect(['index']);
    }
}