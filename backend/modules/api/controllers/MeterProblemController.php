<?php

namespace backend\modules\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

class MeterProblemController extends ActiveController
{
    public $modelClass = 'common\models\Meterproblem';
    public $user=null;

    public function actionFromuser($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['userID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Problems not found'];
        }

        return $recs;
    }

    public function actionFrommeter($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['meterID' => $id])->all();

        if (!$recs) {
            return ['error' => 'Problems not found'];
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
