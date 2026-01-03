<?php

namespace backend\controllers;

use backend\models\Adduserform;
use common\models\Enterprise;
use common\models\User;
use common\models\Userprofile;
use common\models\Technicianinfo;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'except' => ['error'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $search = Yii::$app->request->get('q');
        $detail = Yii::$app->request->get('id');

        $query = \common\models\User::find();
        $detailUser = null;

        // Clean empty search
        if ($search !== null && trim($search) === '') {
            return $this->redirect(['index']);
        }
        // Apply search filter
        if ($search) {
            $query->andWhere(['like', 'username', $search]);
        }
        $users = $query->all();

        if($detail){
            $detailUser = User::findOne($detail);
        }

        return $this->render('index', [
            'users' => $users,
            'search' => $search,
            'addUserModel' => new Adduserform(),
            'addUserProfile' => new Userprofile(),
            'addTechnicianInfo' => new Technicianinfo(),
            'detailUser' => $detailUser,
            'enterpriseList' => Enterprise::find()->all(),
        ]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->user->can('createUser')) {
            throw new ForbiddenHttpException('Não tem permissão para criar utilizadores.');
        }

        $addUserModel = new Adduserform();

        if ($addUserModel->load(Yii::$app->request->post())) {
            try {
                if (!$addUserModel->validate()) {
                    throw new \Exception('Validação falhou: ' . json_encode($addUserModel->getErrors()));
                }

                if (!$addUserModel->createUser()) {
                    throw new \Exception('Erro ao guardar o utilizador.');
                }

                Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso!');
                return $this->redirect(['index']);

            } catch (\Exception $e) {
                Yii::error('CreateUser failed: ' . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'Ação Falhada: ' . $e->getMessage());
            }
        }

        $users = User::find()->all();
        $enterpriseList = Enterprise::find()->all();

        return $this->render('index', [
            'users' => $users,
            'addUserModel' => $addUserModel,
            'detailUser' => null,
            'enterpriseList' => $enterpriseList,
        ]);
    }

    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Utilizador não encontrado.');
            return $this->redirect(['index']);
        }

        // GET AO USERPROFILE
        $profile = $user->userprofile;
        //INFOS DO TECNICO
        $techInfos = $user->technicianinfos ?? [];
        $techInfo = !empty($techInfos) ? $techInfos[0] : new Technicianinfo(['userID' => $user->id]);


        $postData = Yii::$app->request->post();
        if ($user->load($postData) && $profile->load($postData)) {

            if ($profile->birthDate) {
                $profile->birthDate = date('Y-m-d', strtotime($profile->birthDate));
            }

            $valid = $user->validate() && $profile->validate();

            //  VERIFY SE É TECNICO
            $isTechnician = !empty($postData['User']['technicianinfos']) && $postData['User']['technicianinfos'] == '1';

            if ($isTechnician) {
                $techInfo->load($postData);
                $valid = $valid && $techInfo->validate();
            }

            if ($valid) {
                //INICIA A TRANSACTION DO POST
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->save(false);
                    $profile->save(false);

                    //DA SAVE AO TECNICO, SE TROCOU PARA MORADOR DA DELETE
                    if ($isTechnician) {
                        $techInfo->userID = $user->id;
                        $techInfo->save(false);
                    } else {
                        Technicianinfo::deleteAll(['userID' => $user->id]);
                    }
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso!');
                    return $this->redirect(['index']);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::$app->session->setFlash('error', 'Erro ao atualizar utilizador.');
                }
            }
        }

        // Renderizar o formulário de edição
        return $this->render('update', [
            'user' => $user,
            'profile' => $profile,
            'techInfo' => $techInfo,
        ]);
    }

    public function actionUpdateStatus($id)
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if (isset($id, $post['status'])) {
                $user = User::findOne($id);
                if ($user) {
                    $user->status = (int)$post['status'];
                    $user->save(false);
                    Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso!');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('index', [
            'user' => $user
        ]);
    }
}