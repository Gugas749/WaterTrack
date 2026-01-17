<?php

namespace backend\modules\api\controllers;

use Yii;
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

        return $recs;
    }

    public function actionFromenterprise($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['enterpriseID' => $id])->all();

        return $recs;
    }

    public function actionType($id)
    {
        $userprofilemodel = new $this->modelClass;
        $recs = $userprofilemodel::find()->where(['meterTypeID' => $id])->all();

        return $recs;
    }

    public function actionWithstate($state)
    {
        $model = new $this->modelClass;
        $recs = $model::find()->where(['state' => $state])->all();

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

    public function checkAccess($action, $model = null, $params = [])
    {
        if($this->user)
        {
            switch ($action) {
                case 'index': // GETS
                case 'view':
                    break;
                case 'create': // POST
                    if(!Yii::$app->user->can('createMeter')){
                        throw new \yii\web\ForbiddenHttpException('Não tem permissão para efetuar esta operação.');
                    }
                    break;
                case 'delete': // DELETES
                    if(!Yii::$app->user->can('deleteMeter')){
                        throw new \yii\web\ForbiddenHttpException('Não tem permissão para efetuar esta operação.');
                    }
                    break;
                case 'update': // PUT/PATCH
                    if(!Yii::$app->user->can('updateMeter')){
                        throw new \yii\web\ForbiddenHttpException('Não tem permissão para efetuar esta operação.');
                    }
                    break;
            }
        }
    }
}
