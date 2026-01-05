<?php

namespace backend\modules\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

class MeterController extends ActiveController
{
    public $modelClass = 'common\models\Meter';
    public $user=null;

    public function actionFromuser($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['userID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Meters not found'];
        }

        return $recs;
    }

    public function actionFromenterprise($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['enterpriseID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Meters not found'];
        }

        return $recs;
    }

    public function actionType($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['meterTypeID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Meters not found'];
        }

        return $recs;
    }

    public function actionWithstate($state)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['state' => $state])->all();

        if (!$recs) {
            return ['error' => 'Meters not found'];
        }

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
