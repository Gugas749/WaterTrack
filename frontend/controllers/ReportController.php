<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use common\models\Meterproblem;
use common\models\Meter;
use common\models\Technicianinfo;

class ReportController extends Controller
{
    /**
     * Comportamento de acesso
     */
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

        // Obter contadores e problemas
        if ($isTechnician) {
            $enterpriseIds = Technicianinfo::find()
                ->select('enterpriseID')
                ->where(['userID' => $user->id])
                ->column();

            $meters = Meter::find()->where(['enterpriseID' => $enterpriseIds])->all();
            $problems = Meterproblem::find()
                ->where(['meterID' => array_column($meters, 'id')])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        } else {
            $meters = Meter::find()->where(['userID' => $user->id])->all();
            $problems = Meterproblem::find()
                ->where(['userID' => $user->id])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        }

        // Modelo para o right panel
        $model = new Meterproblem();

        $detailID = Yii::$app->request->get('id');
        $detailProblem = $detailID ? Meterproblem::findOne($detailID) : null;

        // Verificar acesso ao detalhe
        if ($detailProblem) {
            if (!$isTechnician && $detailProblem->userID != $user->id) {
                $detailProblem = null;
            } elseif ($isTechnician) {
                $meter = Meter::findOne($detailProblem->meterID);
                $allowedEnterprises = Technicianinfo::find()
                    ->select('enterpriseID')
                    ->where(['userID' => $user->id])
                    ->column();
                if (!in_array($meter->enterpriseID, $allowedEnterprises)) {
                    $detailProblem = null;
                }
            }
        }

        return $this->render('index', [
            'reports' => $problems,
            'isTechnician' => $isTechnician,
            'model' => $model,
            'meters' => $meters,
            'detailProblem' => $detailProblem,
        ]);
    }
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        $model = new Meterproblem();

        // Obter contadores permitidos
        if ($user->isTechnician()) {
            $enterpriseIds = Technicianinfo::find()
                ->select('enterpriseID')
                ->where(['userID' => $user->id])
                ->column();
            $meters = Meter::find()->where(['enterpriseID' => $enterpriseIds])->all();
        } else {
            $meters = Meter::find()->where(['userID' => $user->id])->all();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Validar que o meterID é permitido
            if (!in_array($model->meterID, array_column($meters, 'id'))) {
                throw new \yii\web\BadRequestHttpException('Contador inválido.');
            }

            $model->userID = $user->id;
            $model->tecnicoID = $user->isTechnician() ? $user->id : null;
            $model->problemState = 2; // POR RESOLVER

            if ($model->save()) {
                // Atualizar estado do contador
                $meter = Meter::findOne($model->meterID);
                if ($meter) {
                    $meter->state = 2; // Problema
                    $meter->save(false);
                }

                Yii::$app->session->setFlash('success', 'Relatório criado com sucesso.');
                return $this->redirect(['index']);
            }
        }

        return $this->redirect(['index']);
    }
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;
        if (!$user->isTechnician()) {
            throw new NotFoundHttpException('Acesso negado.');
        }

        $problem = Meterproblem::findOne($id);
        if (!$problem) {
            throw new NotFoundHttpException('Relatório não encontrado.');
        }

        $meter = Meter::findOne($problem->meterID);
        $enterpriseIds = Technicianinfo::find()->select('enterpriseID')->where(['userID' => $user->id])->column();
        if (!in_array($meter->enterpriseID, $enterpriseIds)) {
            throw new NotFoundHttpException('Acesso negado.');
        }

        if ($problem->load(Yii::$app->request->post())) {
            // Técnico assume o reparo se ainda não tiver técnico
            if (!$problem->tecnicoID) {
                $problem->tecnicoID = $user->id;
            }

            if ($problem->save()) {
                // Atualizar estado do contador
                $meter->state = $problem->problemState == 0 ? 1 : 2;
                $meter->save(false);

                Yii::$app->session->setFlash('success', 'Problema atualizado com sucesso.');
                return $this->redirect(['index', 'id' => $problem->id]);
            }
        }

        return $this->redirect(['index']);
    }
}
