<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Userprofile;
use yii\base\DynamicModel;

class SettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $profile = $user->userprofile ?: new Userprofile(['userID' => $user->id]);

        $passwordModel = new DynamicModel(['oldPassword', 'newPassword', 'newPasswordRepeat']);
        $passwordModel->addRule(['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required');
        $passwordModel->addRule('newPassword', 'string', ['min' => 6]);
        $passwordModel->addRule('newPasswordRepeat', 'compare', [
            'compareAttribute' => 'newPassword',
            'message' => 'As passwords não coincidem.'
        ]);

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
        $user = Yii::$app->user->identity;

        $passwordModel = new DynamicModel(['oldPassword', 'newPassword', 'newPasswordRepeat']);
        $passwordModel->addRule(['oldPassword', 'newPassword', 'newPasswordRepeat'], 'required');
        $passwordModel->addRule('newPassword', 'string', ['min' => 8]);
        $passwordModel->addRule('newPasswordRepeat', 'compare', [
            'compareAttribute' => 'newPassword',
            'message' => 'As passwords não coincidem.'
        ]);

        if ($passwordModel->load(Yii::$app->request->post()) && $passwordModel->validate()) {
            if (!$user->validatePassword($passwordModel->oldPassword)) {
                $passwordModel->addError('oldPassword', 'Password antiga incorreta.');
                Yii::$app->session->setFlash('error', 'Password antiga incorreta.');
            } else {
                $user->setPassword($passwordModel->newPassword);
                $user->save(false);
                Yii::$app->session->setFlash('success', 'Password alterada com sucesso.');
                return $this->redirect(['index']);
            }
        }

        return $this->redirect(['index']);
    }
}
