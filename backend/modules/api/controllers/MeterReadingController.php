<?php

namespace backend\modules\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

class MeterReadingController extends ActiveController
{
    public $modelClass = 'common\models\Meterreading';
    public $user=null;

    public function actionFromuser($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['userID' => $id])->one();

        return $recs;
    }

    public function actionFrommeter($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['meterID' => $id])->all();

        return $recs;
    }

    public function actionProblem($id)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['problemID' => $id])->one();

        return $recs;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'except' => ['index', 'view'], //Excluir aos GETs
            'auth' => [$this, 'auth']
        ];
        return $behaviors;
    }

    public function auth($username, $password)
    {
        $user = \common\models\User::findByUsername($username);
        if ($user && $user->validatePassword($password))
        {
            $this->user=$user; //Guardar user autenticado
            return $user;
        }
        throw new \yii\web\ForbiddenHttpException('No authentication'); //403
    }
}
