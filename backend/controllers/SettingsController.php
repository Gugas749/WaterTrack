<?php

namespace backend\controllers;

use common\models\Resetpasswordform;
use common\models\Userprofile;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\Controller;

class SettingsController extends Controller
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
        $user = Yii::$app->user->identity;
        $profile = $user->userprofile ?: new Userprofile(['userID' => $user->id]);

        $passwordModel = new Resetpasswordform();

        return $this->render('index', [
            'user' => $user,
            'profile' => $profile,
            'passwordModel' => $passwordModel
        ]);
    }
    public function actionUpdate()
    {
        $user = Yii::$app->user->identity;
        $profile = $user->userprofile ?: new Userprofile(['userID' => $user->id]);

        if ($user->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            $valid = $user->validate() && $profile->validate();
            if ($valid) {
                $user->save(false);
                $profile->save(false);
                Yii::$app->session->setFlash('success', 'Perfil atualizado com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao atualizar o perfil.');
            }
        }

        return $this->redirect(['index']);
    }
    public function actionResetPassword()
    {
        $model = new Resetpasswordform();

        $model->username = Yii::$app->user->identity->username;

        if ($model->load(Yii::$app->request->post()) && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Password atualizado com sucesso.');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Erro ao alterar a password.');
        return $this->redirect(['index']);
    }
}
