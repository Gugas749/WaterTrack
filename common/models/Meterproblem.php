<?php

namespace common\models;

use common\mosquitto\phpMQTT;
use Yii;

/**
 * This is the model class for table "meterproblem".
 *
 * @property int $id
 * @property int $meterID
 * @property int $userID
 * @property int|null $tecnicoID
 * @property int $problemState
 * @property string $description
 *
 * @property Meter $meter
 * @property User $tecnico
 * @property User $user
 */
class Meterproblem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meterproblem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tecnicoID'], 'default', 'value' => null],
            [['meterID', 'userID', 'problemState', 'description'], 'required'],
            [['meterID', 'userID', 'tecnicoID', 'problemState'], 'integer'],
            [['description'], 'string', 'max' => 100],
            [['meterID'], 'exist', 'skipOnError' => true, 'targetClass' => Meter::class, 'targetAttribute' => ['meterID' => 'id']],
            [['tecnicoID'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['tecnicoID' => 'id']],
            [['userID'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userID' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'meterID' => 'Meter ID',
            'userID' => 'User ID',
            'tecnicoID' => 'Tecnico ID',
            'problemState' => 'Problem State',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[Meter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeter()
    {
        return $this->hasOne(Meter::class, ['id' => 'meterID']);
    }

    /**
     * Gets query for [[Tecnico]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTecnico()
    {
        return $this->hasOne(User::class, ['id' => 'tecnicoID']);
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $myObj = new \stdClass();
        $myObj->id = $this->id;
        $myObj->tecnicoID = $this->tecnicoID;
        $myObj->description = $this->description;
        $myObj->problemState = $this->problemState;

        $myJSON = json_encode($myObj);

        if ($insert) {
            $this->fazPublishNoMosquitto("METERPROB_INSERT", $myJSON);
        } else {
            $this->fazPublishNoMosquitto("METERPROB_UPDATE", $myJSON);
        }
    }
    public function afterDelete()
    {
        parent::afterDelete();

        $myObj = new \stdClass();
        $myObj->id = $this->id;

        $myJSON = json_encode($myObj);

        $this->fazPublishNoMosquitto("METERPROB_DELETE", $myJSON);
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
