<?php

namespace common\models;

use common\models\User;
use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "technicianinfo".
 *
 * @property int $id
 * @property int $userID
 * @property int $enterpriseID
 * @property string $profissionalCertificateNumber
 *
 * @property Enterprise $enterprise
 * @property User $user
 */
class Technicianinfo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'technicianinfo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enterpriseID', 'profissionalCertificateNumber'], 'required',
                'when' => function ($model) {
                    // server-side condition
                    return $model->user && $model->user->isTechnician();
                },
                'whenClient' => "function () {
                // client-side condition
                return $('#user-type-dropdown').val() === '1';
            }"
            ],

            [['userID', 'enterpriseID'], 'integer'],
            [['profissionalCertificateNumber'], 'string', 'max' => 100],

            [['enterpriseID'], 'exist',
                'skipOnError' => true,
                'targetClass' => Enterprise::class,
                'targetAttribute' => ['enterpriseID' => 'id']
            ],
            [['userID'], 'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['userID' => 'id']
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userID' => 'User ID',
            'enterpriseID' => 'Enterprise ID',
            'profissionalCertificateNumber' => 'Profissional Certificate Number',
        ];
    }

    /**
     * Gets query for [[Enterprise]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEnterprise()
    {
        return $this->hasOne(Enterprise::class, ['id' => 'enterpriseID']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userID']);
    }

    //----------------------------------------------------------------------------------------
    //                         MOSQUITTO
    //----------------------------------------------------------------------------------------
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->enterpriseID = $this->enterpriseID;
        $myObj->profissionalCertificateNumber = $this->profissionalCertificateNumber;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("TECHINFO_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("TECHINFO_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("TECHINFO_DELETE", $myJSON);
    }
    public function fazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";
        $port = 1883;
        $client_id = "yii2-user-" . uniqid();

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect()) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        } else {
            file_put_contents(
                Yii::getAlias('@runtime') . '/mqtt_error.log',
                "Erro MQTT\n",
                FILE_APPEND
            );
        }
    }
}
