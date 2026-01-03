<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Meter;
use common\models\Meterproblem;
use common\models\Meterreading;



class DashboardController extends Controller
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
        $userId = $user->id;
        $isTechnician = $user ? $user->isTechnician() : false;

        $meterQuery = Meter::find();

        if (!$isTechnician) {
            $meterQuery->andWhere(['userID' => $userId]);
        }

        $ativos       = (clone $meterQuery)->andWhere(['state' => 1])->count();
        $comProblema  = (clone $meterQuery)->andWhere(['state' => 2])->count();
        $inativos     = (clone $meterQuery)->andWhere(['state' => 0])->count();

        // ===== LEITURAS (Meterreading.problemState) =====
        $readingQuery = Meterreading::find()
            ->joinWith('meter');

        if (!$isTechnician) {
            $readingQuery->andWhere(['meter.userID' => $userId]);
        }


        $readingsComProblema = (clone $readingQuery)
            ->andWhere(['meterreading.problemState' => 1])
            ->count();

        $readingsSemProblema = (clone $readingQuery)
            ->andWhere(['meterreading.problemState' => 0])
            ->count();

        return $this->render('index', [
            'ativos' => $ativos,
            'comProblema' => $comProblema,
            'inativos' => $inativos,

            // 🔥 novos dados
            'readingsComProblema' => $readingsComProblema,
            'readingsSemProblema' => $readingsSemProblema,

            'isTechnician' => $isTechnician,
        ]);
    }
}
