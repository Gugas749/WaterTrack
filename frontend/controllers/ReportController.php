<?php
namespace frontend\controllers;

use common\models\Meterreading;
use Yii;
use yii\web\Controller;
use common\models\Meterproblem;
use common\models\Meter;

class ReportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
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
        $id = Yii::$app->request->get('id');

        $query = Meterproblem::find()
            ->joinWith(['meter', 'user']);

        if (!$isTechnician) {
            $query->andWhere(['meter.userID' => $user->id]);
        }

        $reports = $query->all();

        $detailReport = null;
        if ($id) {
            $detailReport = Meterproblem::findOne($id);

            if (
                !$isTechnician &&
                $detailReport &&
                $detailReport->meter->userID !== $user->id
            ) {
                $detailReport = null;
            }
        }

        return $this->render('index', [
            'reports'      => $reports,
            'detailReport' => $detailReport,
            'isTechnician' => $isTechnician,
        ]);
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        if (!$user->isTechnician()) {
            throw new ForbiddenHttpException();
        }

        $model = new Meterproblem();

        if ($model->load(Yii::$app->request->post())) {

            // 🔹 associar o utilizador do contador
            $meter = Meter::findOne($model->meterID);
            if (!$meter) {
                Yii::$app->session->setFlash('error', 'Contador inválido.');
                return $this->redirect(['index']);
            }

            $model->userID = $meter->userID;

            // 🔹 tratar "Outro"
            $otherProblem = Yii::$app->request->post('otherProblem');
            if ($model->problemType === 'Outro' && !empty($otherProblem)) {
                $model->problemType = $otherProblem;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Relatório criado.');
                return $this->redirect(['index']);
            }

            // 🔥 DEBUG REAL
            Yii::$app->session->setFlash('error', json_encode($model->errors));
        }

        return $this->redirect(['index']);
    }


    public function actionUpdate($id)
    {
        if (!Yii::$app->user->identity->isTechnician()) {
            throw new ForbiddenHttpException();
        }

        $model = Meterproblem::findOne($id);
        if (!$model) {
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Relatório atualizado.');
        }

        return $this->redirect(['index']);
    }
}
