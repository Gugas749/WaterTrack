<?php

namespace backend\modules\api\controllers;

use common\models\User;
use common\models\Userprofile;
use Yii;
use yii\rest\Controller;

class AuthController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }
    public function verbs()
    {
        return [
            'login' => ['POST'],
            'signup' => ['POST'],
        ];
    }
    public function actionLogin()
    {
        $body = Yii::$app->request->post();
        $username = $body['username'] ?? null;
        $password = $body['password'] ?? null;

        if (!$username || !$password) {
            return [
                "success" => 2,
                "message" => "Username and password required"
            ];
        }

        // Find user by username or email
        $user = User::findOne(['username' => $username]);

        if (!$user) {
            return [
                "success" => 3,
                "message" => "User not found"
            ];
        }

        // Validate password
        if (!Yii::$app->security->validatePassword($password, $user->password_hash)) {
            return [
                "success" => 4,
                "message" => "Incorrect password"
            ];
        }

        $profile = $user->userprofile;
        $techInfo = $user->technicianinfos;

        return [
            "success" => 0,
            "user" => [
                "userId" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "status" => $user->status,

                "birthDate" => $profile->birthDate ?? null,
                "address" => $profile->address ?? null,
                "profileID" => $profile->id ?? null,

                "enterpriseID" => $techInfo->enterpriseID ?? null,
                "certificationNumber" => $techInfo->profissionalCertificateNumber ?? null,
            ]
        ];
    }
    public function actionSignup(){
        $body = Yii::$app->request->post();

        if (empty($body['password']) || empty($body['email'])) {
            return [
                "success" => 0,
                "message" => "Empty fields."
            ];
        }

        // Check email
        $existingEmail = User::find()->where(['email' => $body['email']])->one();
        if ($existingEmail) {
            if ($existingEmail->status == 9) {
                return [
                    "success" => 0,
                    "message" => "Your account already exists but is not verified. Please check your email."
                ];
            }

            return [
                "success" => 0,
                "message" => "Email already exists"
            ];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = new User();
            $user->username = "N/A";
            $user->email = $body['email'];
            $user->setPassword($body['password']);
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();

            if (!$user->save()) {
                return [
                    "success" => 0,
                    "message" => "User save failed",
                    "errors" => $user->getErrors()
                ];
            }

            // Assign role
            $auth = Yii::$app->authManager;
            $role = $auth->getRole('resident');
            $auth->assign($role, $user->id);

            // Create default profile
            $profile = new Userprofile();
            $profile->userID = $user->id;
            $profile->name = 'N/A';
            $profile->birthDate = '2000-01-01';
            $profile->address = 'N/A';

            if (!$profile->save()) {
                return [
                    "success" => 0,
                    "message" => "Profile save failed",
                    "errors" => $profile->getErrors()
                ];
            }

            $transaction->commit();

            return [
                "success" => 1,
                "message" => "User created"
            ];

        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'signup');

            return [
                "success" => 1,
                "message" => "Internal error",
                "debug" => YII_DEBUG ? $e->getMessage() : null
            ];
        }
    }
    public function actionTeste()
    {
        return [
            'success' => true,
            'message' => 'AuthController is reachable!',
        ];
    }
}
