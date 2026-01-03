<?php
namespace frontend\controllers;

use backend\models\Addmeterform;
use common\models\Enterprise;
use common\models\Metertype;
use common\models\Technicianinfo;
use common\models\User;
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

        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');

        if ($isTechnician) {
            $enterpriseIds = TechnicianInfo::find()
                ->select('enterpriseID')
                ->where(['userID' => $user->id])
                ->column();

            if (empty($enterpriseIds)) {
                $query = Meter::find()->where('0=1');
            } else {
                $query = Meter::find()->where([
                    'enterpriseID' => $enterpriseIds
                ]);
            }
        } else {
            $query = Meter::find()->where([
                'userID' => $user->id
            ]);
        }

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'address', $search]);
        }
        $meters = $query->all();

        $detailMeter = null;
        if ($detail) {
            $detailMeter = Meter::findOne($detail);
            if ($detailMeter) {
                if (!$isTechnician && $detailMeter->userID !== $user->id) {
                    $detailMeter = null;
                }
            }
        }

        return $this->render('index', [
            'meters' => $meters,
            'search' => $search,
            'users' => User::find()->all(),
            'meterTypes' => MeterType::find()->all(),
            'enterprises' => Enterprise::find()->all(),
            'detailMeter' => $detailMeter,
            'addMeterModel' => new AddMeterForm(),
            'isTechnician' => $isTechnician,
        ]);
    }

    public function actionCreate()
    {
        $model = new Addmeterform();
        $user = Yii::$app->user->identity;

        if ($model->load(Yii::$app->request->post())) {

            if ($user->isTechnician()) {
                $enterpriseId = TechnicianInfo::find()
                    ->select('enterpriseID')
                    ->where(['userID' => $user->id])
                    ->scalar();

                $model->enterpriseID = $enterpriseId;
            }

            if ($model->createmeter()) {
                Yii::$app->session->setFlash('success', 'Contador criado com sucesso!');
                return $this->redirect(['index']);
            }
        }

        Yii::$app->session->setFlash('error', 'Ação Falhada: Contactar Administrador [M-1]');
        return $this->redirect(['index']);
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

}
