<?php

namespace backend\modules\api\controllers;

use yii\filters\auth\HttpBasicAuth;
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'common\models\User'; // CRUD
    public $user=null;

    public function actionPutstatus($id)
    {
        $status=\Yii::$app->request->post('status');
        $climodel = new $this->modelClass;
        $ret = $climodel::findOne(['id' => $id]);
        if($ret)
        {
            $ret->status = $status;
            $ret->save();
        }
        else
        {
            throw new \yii\web\NotFoundHttpException("Nome de user não existe");
        }
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
