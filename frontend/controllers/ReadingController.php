<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Meterreading;
use common\models\Meter;


class ReadingController extends Controller
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
        $isTechnician = $user ? $user->isTechnician() : false;

        $meterIdParam   = Yii::$app->request->get('meterID');
        $readingIdParam = Yii::$app->request->get('id');

        // técnicos podem ver todas; moradores só dos seus contadores
        if ($isTechnician) {
            $query = Meterreading::find();
        } else {
            $query = Meterreading::find()
                ->joinWith('meter')
                ->where(['meter.userID' => $user->id]);
        }

        if (!empty($meterIdParam)) {
            $query->andWhere(['meterID' => $meterIdParam]);
        }

        $readings = $query->all();

        // detalhe — garante que o detail pertence ao user, excepto se technician
        $detailReading = null;
        if ($readingIdParam) {
            $detailReading = Meterreading::findOne($readingIdParam);
            if ($detailReading) {
                if (
                    !$isTechnician &&
                    $detailReading->meter->userID !== $user->id
                ) {
                    $detailReading = null;
                }
            }
        }

        return $this->render('index', [
            'readings'      => $readings,
            'detailReading' => $detailReading,
            'isTechnician'  => $isTechnician,
        ]);
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        if (!$user->isTechnician()) {
            throw new ForbiddenHttpException('Sem permissão para criar leituras.');
        }

        $model = new Meterreading();
        $model->date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {

            // 🔹 Buscar o contador selecionado
            $meter = Meter::findOne($model->meterID);

            if (!$meter) {
                Yii::$app->session->setFlash('error', 'Contador inválido.');
                return $this->redirect(['index']);
            }

            // 🔹 Associar o userID DO CONTADOR
            $model->userID = $meter->userID;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Leitura criada com sucesso!');
                return $this->redirect(['index', 'id' => $model->id]);
            }
        }

        Yii::$app->session->setFlash(
            'error',
            'Falha ao criar leitura: ' . json_encode($model->errors)
        );

        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        // apenas técnicos podem actualizar
        if (!Yii::$app->user->identity->isTechnician()) {
            throw new ForbiddenHttpException('Sem permissão para actualizar leituras.');
        }

        $model = Meterreading::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Leitura não encontrada.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Leitura atualizada com sucesso!');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Falha ao atualizar leitura.');
        return $this->redirect(['index', 'id' => $id]);
    }

    public function actionGetUserByMeter($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $meter = Meter::findOne($id);

        if ($meter) {
            return ['userID' => $meter->userID];
        }

        return ['userID' => null];
    }
}
