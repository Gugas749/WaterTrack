<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Meter;
class MeterController extends Controller
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

        $queryParam = Yii::$app->request->get('q');
        $meterIdParam = Yii::$app->request->get('id');

        // técnicos podem ver todos; moradores só os seus
        if ($isTechnician) {
            $query = Meter::find();
        } else {
            $query = Meter::find()->where(['userID' => $user->id]);
        }

        if (!empty($queryParam)) {
            $query->andWhere(['like', 'address', $queryParam]);
        }

        $meters = $query->all();

        // detalhe — garante que o detail pertence ao user, excepto se technician
        $detailMeter = null;
        if ($meterIdParam) {
            $detailMeter = Meter::findOne($meterIdParam);
            if ($detailMeter) {
                if (!$isTechnician && $detailMeter->userID !== $user->id) {
                    // morador tentando ver detalhe de outro — negar
                    $detailMeter = null;
                }
            }
        }

        return $this->render('index', [
            'meters' => $meters,
            'detailMeter' => $detailMeter,
            'isTechnician' => $isTechnician,
        ]);
    }

    public function actionCreate()
    {
        // apenas técnicos podem criar
        if (!Yii::$app->user->identity->isTechnician()) {
            throw new ForbiddenHttpException('Sem permissão para criar contadores.');
        }

        $model = new Meter();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Contador criado com sucesso!');
            return $this->redirect(['index']);
        }

        // se falhar, re-render index (poderias devolver erros via session/flashes)
        Yii::$app->session->setFlash('error', 'Falha ao criar contador.');
        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        // apenas técnicos podem actualizar
        if (!Yii::$app->user->identity->isTechnician()) {
            throw new ForbiddenHttpException('Sem permissão para actualizar contadores.');
        }

        $model = Meter::findOne($id);
        if (!$model) {
            Yii::$app->session->setFlash('error', 'Contador não encontrado.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Contador atualizado com sucesso!');
            return $this->redirect(['index']);
        }

        // se chegou aqui, volta ao index mostrando o detail panel com erros (opcional)
        Yii::$app->session->setFlash('error', 'Falha ao atualizar contador.');
        return $this->redirect(['index', 'id' => $id]);
    }

}
