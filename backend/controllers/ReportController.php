<?php

namespace backend\controllers;

use common\models\Enterprise;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use common\models\Meterproblem;
use common\models\Meter;
use common\models\User;
use common\models\Technicianinfo;

class ReportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'], // apenas admins podem aceder ao backend
                    ],
                ],
                'denyCallback' => fn() => $this->redirect(['site/login']),
            ],
        ];
    }

    public function actionIndex()
    {
        $search = Yii::$app->request->get('q');
        $detailId = Yii::$app->request->get('id');

        $detailProblem = null;
        $detailsMeter = null;
        $detailsEnterprise = null;
        $detailsTechnicians = [];

        // Obter todos os relatórios
        $query = Meterproblem::find()->orderBy(['id' => SORT_DESC]);

        if ($search) {
            $query->joinWith('meter')->andWhere(['like', 'meter.address', $search]);
        }

        $reports = $query->all();

        // Obter todos os contadores para o right panel
        $meters = Meter::find()->all();

        // Modelo para criar novo relatório
        $model = new Meterproblem();

        // Detalhe do relatório, se id for fornecido
        if($detailId != null && $detailId > 0){
            $detailProblem = Meterproblem::findOne($detailId);
            $detailsMeter = $detailProblem->meter;
            $detailsEnterprise = Enterprise::findOne($detailsMeter->enterprise);

            $technicianIds = Technicianinfo::find()->select('userID')->column();
            $aux = User::find()->where(['id' => $technicianIds])->all();

            foreach ($aux as $user) {
                if($user->technicianinfos->enterpriseID == $detailsMeter->enterpriseID) {
                    $detailsTechnicians = array_merge($detailsTechnicians, [$user]);
                }
            }
        }

        return $this->render('index', [
            'reports' => $reports,
            'meters' => $meters,
            'model' => $model,
            'search' => $search,
            'detailProblem' => $detailProblem,
            'detailsMeter' => $detailsMeter,
            'detailsEnterprise' => $detailsEnterprise,
            'detailsTechnicians' => $detailsTechnicians,
        ]);
    }

    public function actionCreate()
    {
        $model = new Meterproblem();
        $user = Yii::$app->user->identity;

        $model->userID = $user->id;

        $meters = Meter::find()->all();
        $technicianIds = Technicianinfo::find()->select('userID')->column();
        $technicians = User::find()->where(['id' => $technicianIds])->all();

        if ($model->load(Yii::$app->request->post())) {

            // Validar meterID
            if (!in_array($model->meterID, array_map(fn($m) => $m->id, $meters))) {
                Yii::$app->session->setFlash('error', 'Contador inválido.');
                return $this->redirect(['index']);
            }

            $model->tecnicoID = $model->tecnicoID ?: null;
            $model->problemState = 2; // POR RESOLVER

            if ($model->save()) {

                // **Atualizar estado do contador**
                $meter = Meter::findOne($model->meterID);
                if ($meter) {
                    $meter->state = 2; // 2 = em problema, ajusta conforme tua lógica
                    $meter->save(false); // false para não validar novamente
                }

                Yii::$app->session->setFlash('success', 'Relatório criado com sucesso.');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Falha ao criar relatório.');
            }
        }

        return $this->redirect(['index']);
    }


    public function actionUpdate($id)
    {
        $model = Meterproblem::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Relatório não encontrado.');
        }

        $technicianIds = Technicianinfo::find()->select('userID')->column();
        $technicians = User::find()->where(['id' => $technicianIds])->all();

        if ($model->load(Yii::$app->request->post())) {

            $model->tecnicoID = $model->tecnicoID ?: null;

            if ($model->save()) {

                // Obter o contador associado
                $meter = Meter::findOne($model->meterID);

                if ($meter) {

                    if ($model->problemState == 0) { // RESOLVIDO

                        // Verificar se existem outros relatórios ativos para este contador
                        $activeReports = Meterproblem::find()
                            ->where(['meterID' => $meter->id])
                            ->andWhere(['in', 'problemState', [1, 2]]) // EM ANÁLISE ou POR RESOLVER
                            ->andWhere(['<>', 'id', $model->id]) // ignorar o relatório atual
                            ->count();

                        if ($activeReports == 0) {
                            // Nenhum relatório ativo, pode voltar a normal
                            $meter->state = 1; // normal
                        }
                        // se houver relatórios ativos, mantém em problema (2)
                    } else {
                        // EM ANÁLISE ou POR RESOLVER → contador sempre em problema
                        $meter->state = 2;
                    }

                    $meter->save(false);
                }

                Yii::$app->session->setFlash('success', 'Relatório atualizado com sucesso.');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Falha ao atualizar relatório.');
            }
        }

        return $this->redirect(['index']);
    }


}
